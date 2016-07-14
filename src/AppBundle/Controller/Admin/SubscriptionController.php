<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Subscription;
use AppBundle\Entity\SubscriptionRepository;
use AppBundle\Form\Admin\SubscriptionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SURFnet\SPRegistration\Grid\GridConfiguration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GridController
 *
 * @Route("/admin/subscriptions")
 */
class SubscriptionController extends Controller implements SecuredController
{
    /**
     * @Route("/", name="admin.subscription.overview")
     *
     * @return Response
     */
    public function overviewAction()
    {
        return $this->render('admin/subscription/overview.html.twig');
    }

    /**
     * @Route("/grid", name="admin.subscription.grid")
     *
     * @return Response
     */
    public function gridAction()
    {
        $configuration = new GridConfiguration();
        $grid = $configuration->configureGrid(
            $this->get('grid'),
            $this->generateUrl('admin.subscription.grid')
        );
        return $grid->getGridResponse('admin/subscription/grid.html.twig');
    }

    /**
     * @Route("/new", name="admin.subscription.new")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $subscription = new Subscription();

        $form = $this->createForm(
            new SubscriptionType(),
            $subscription,
            array()
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('subscription.repository')->insert($subscription);

            $this->get('mail.manager')->sendInvitation($subscription);
            $this->get('mail.manager')->sendCreatedNotification($subscription);

            return $this->redirect($this->generateUrl('admin.subscription.overview'));
        }

        return $this->render(
            'admin/subscription/new.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
            )
        );
    }

    /**
     * @Route("/{id}", name="admin.subscription.view")
     *
     * @param string $id
     *
     * @return Response
     */
    public function viewAction($id)
    {
        $subscription = $this->get('subscription.repository')->findById($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            'admin/subscription/view.html.twig',
            array(
                'subscription' => $subscription,
                'metadataUrlSubject' => $this->getCertSubject(
                    $subscription->getMetadataUrl()
                ),
                'acsLocationSubject' => $this->getCertSubject(
                    $subscription->getAcsLocation()
                ),
            )
        );
    }

    /**
     * @Route("/{id}/finish", name="admin.subscription.finish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function finishAction($id)
    {
        $subscription = $this->get('subscription.repository')->findById($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        $originalSubscription = clone $subscription;
        $subscription->finish();

        $this->get('subscription.repository')->update(
            $originalSubscription,
            $subscription
        );

        $this->get('mail.manager')->sendFinishedNotification($subscription);

        return $this->redirect($this->generateUrl('admin.subscription.overview'));
    }

    /**
     * @Route("/{id}/archive", name="admin.subscription.archive")
     *
     * @param string $id
     *
     * @return Response
     */
    public function archiveAction($id)
    {
        $subscription = $this->get('subscription.repository')->findById($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        $originalSubscription = clone $subscription;
        $subscription->archive();

        $this->get('subscription.repository')->update(
            $originalSubscription,
            $subscription
        );

        return $this->redirect($this->generateUrl('admin.subscription.overview'));
    }

    /**
     * @Route("/{id}/publish", name="admin.subscription.publish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function publishAction($id)
    {
        $subscriptionRepository = $this->get('subscription.repository');
        $subscription = $subscriptionRepository->findById($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        $subscription = $this->linkSubscriptionToJanusConnection(
            $subscriptionRepository,
            $subscription
        );

        // And update the subscription.
        $subscriptionRepository->update(
            clone $subscription,
            $subscription->revertToPublished()
        );

        return $this->redirectToRoute('admin.subscription.overview');
    }

    /**
     * @Route("/janus/{eid}", name="admin.subscription.janus")
     *
     * @param string $eid
     *
     * @return Response
     */
    public function redirectToJanusAction($eid)
    {
        $url = $this->container->getParameter('janus_url');
        $urlParts = parse_url($url);

        $newUrl = $urlParts['scheme'];
        $newUrl .= '://';
        $newUrl .= $urlParts['host'];
        $newUrl .= '/simplesaml/module.php/janus/editentity.php?eid=' . $eid;

        return $this->redirect($newUrl);
    }

    /**
     * Get the certificate subject from a given URL.
     *
     * @param string $url URL to get cert subject from.
     * @return string Certificate subject or empty string
     */
    private function getCertSubject($url)
    {
        if (!$url) {
            return '';
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== 'https') {
            return '';
        }
        $hostname = parse_url($url, PHP_URL_HOST);

        $hostDto = $this->get('ssllabs.analyze_service')->analyze(
            $hostname,
            true
        );

        foreach ($hostDto->endpoints as $endpoint) {
            if (!isset($endpoint->details['cert']['subject'])) {
                continue;
            }
            return $endpoint->details['cert']['subject'];
        }

        return '';
    }

    /**
     * @param SubscriptionRepository $subscriptionRepository
     * @param Subscription $subscription
     * @return Subscription
     */
    private function linkSubscriptionToJanusConnection(
        SubscriptionRepository $subscriptionRepository,
        Subscription $subscription
    ) {
        // Try to find the janus id from the subscription.
        $connectionRepository = $this->get('janus.connection_repository');
        $connection = $connectionRepository->findById(
            $subscription->getJanusId()
        );

        // If we found it, then we're all good.
        if ($connection) {
            return $subscription;
        }

        // If not, we must go deeper by looking for a connection by entityid.
        $connectionDescriptorRepository = $this->get(
            'janus.connection_descriptor_repository'
        );
        $connectionDescriptor = $connectionDescriptorRepository->fetchByName(
            $subscription->getEntityId()
        );

        // Assume we found nothing and need to set the id to NULL
        // (this will force creation of a new janus connection).
        $connectionId = null;
        // But if we did find a connection for the entity id, use that one.
        if ($connectionDescriptor) {
            $connectionId = $connectionDescriptor->getId();
        }

        // And update the subscription.
        $subscriptionRepository->update(
            clone $subscription,
            $subscription->setJanusId($connectionId)
        );

        return $subscription;
    }
}
