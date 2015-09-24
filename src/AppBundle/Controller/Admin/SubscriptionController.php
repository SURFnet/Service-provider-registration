<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Subscription;
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
            $this->get('subscription.manager')->saveSubscription($subscription);

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
        $subscription = $this->get('subscription.manager')->getSubscription($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            'admin/subscription/view.html.twig',
            array(
                'subscription' => $subscription,
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
        $subscription = $this->get('subscription.manager')->getSubscription($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        $subscription->finish();

        $this->get('subscription.manager')->updateSubscription($subscription);

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
        $subscription = $this->get('subscription.manager')->getSubscription($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        $subscription->archive();

        $this->get('subscription.manager')->updateSubscription($subscription);

        return $this->redirect($this->generateUrl('admin.subscription.overview'));
    }

    /**
     * @Route("/janus/{eid}", name="admin.subscription.janus")
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
}
