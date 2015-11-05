<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Form\SubscriptionType;
use AppBundle\Form\SubscriptionTypeFactory;
use AppBundle\Manager\SubscriptionManager;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class FormController
 *
 * @Route("/subscription")
 *
 * @todo: some actions are different only based on the template.. refactor this
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SubscriptionController extends Controller
{
    /**
     * @Route("/{id}", name="form")
     * @Method("GET")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function getAction($id, Request $request)
    {
        try {
            $subscription = $this->getSubscription($id, true, false);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        /** @var SubscriptionTypeFactory $formFactory */
        $formFactory = $this->get('subscription.form.factory');
        $form = $formFactory->buildForm($subscription, $request);

        return $this->render(
            'subscription/form.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
                'locked'       => !$this->get('lock.manager')->lock($id),
            )
        );
    }

    /**
     * @Route("/{id}/save", name="save")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function saveAction($id, Request $request)
    {
        $subscription = $this->getSubscription($id);

        if (!$subscription->isDraft()) {
            throw new InvalidArgumentException('(auto)save is only allowed for drafts');
        }

        /** @var SubscriptionTypeFactory $formFactory */
        $formFactory = $this->get('subscriptionTypeFactory');
        $form = $formFactory->buildForm($subscription, $request);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->get('subscription.manager')->updateSubscription($subscription);
        }

        return new Response();
    }

    /**
     * @Route("/{id}/validate", name="validate")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     *
     * @todo: clean, use recursive method
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateAction($id, Request $request)
    {
        $subscription = $this->getSubscription($id);

        /** @var SubscriptionTypeFactory $formFactory */
        $formFactory = $this->get('subscription.form.factory');
        $form = $formFactory->buildForm($subscription, $request, false);

        $form->submit($request->get($form->getName()), false);

        $response = array('data' => array(), 'errors' => array());

        foreach ($form as $field) {
            if ($field->count() > 1) {
                foreach ($field as $child) {
                    if ($child->isSubmitted()) {
                        $response['data'][$field->getName()][$child->getName()] = $child->getData();
                    }

                    if (!$child->isValid()) {
                        foreach ($child->getErrors(true) as $error) {
                            $response['errors'][$field->getName()][$child->getName()][] = $error->getMessage();
                        }
                    }
                }
            } else {
                if ($field->isSubmitted()) {
                    $response['data'][$field->getName()] = $field->getData();
                }

                if (!$field->isValid()) {
                    foreach ($field->getErrors(true) as $error) {
                        $response['errors'][$field->getName()][] = $error->getMessage();
                    }
                }
            }
        }

        return new JsonResponse($response, $form->isValid() ? 200 : 400);
    }

    /**
     * @Route("/{id}/lock", name="lock")
     *
     * @param string $id
     *
     * @return Response
     */
    public function lockAction($id)
    {
        if (!$this->get('lock.manager')->lock($id)) {
            return new Response('', 423);
        }

        return new Response();
    }

    /**
     * @Route("/{id}")
     * @Method("POST")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function postAction($id, Request $request)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        /** @var SubscriptionTypeFactory $formFactory */
        $formFactory = $this->get('subscription.form.factory');
        $form = $formFactory->buildForm($subscription, $request);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render(
                'subscription/form.html.twig',
                array(
                    'subscription' => $subscription,
                    'form' => $form->createView(),
                    'locked' => !$this->get('lock.manager')->lock($id),
                )
            );
        }

        /** @var SubscriptionManager $subscriptionManager */
        $subscriptionManager = $this->get('subscription.manager');

        // If we're moving from published to finished, simply redirect.
        $requestedState = $request->get('subscription[requestedState]', null, true);
        if ($subscription->isPublished() && $requestedState === Subscription::STATE_FINISHED) {
            return $this->redirect($this->generateUrl('overview_finish', array('id' => $id)));
        }

        // Otherwise if already published remember the changes in the session and redirect.
        if ($subscription->isPublished()) {
            $subscriptionManager->storeSubscriptionInSession($subscription, $request->getSession());

            return $this->redirect($this->generateUrl('overview_update', array('id' => $id)));
        }

        // If in draft an explicit save is always an intent to publish,
        // save and redirect to publish overview.
        $subscriptionManager->updateSubscription($subscription);

        return $this->redirect($this->generateUrl('overview_publish', array('id' => $id)));
    }

    /**
     * @Route("/{id}/overview", name="overview_publish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function overviewForPublicationAction($id)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        return $this->render(
            'subscription/publish_overview.html.twig',
            array(
                'subscription' => $subscription,
            )
        );
    }

    /**
     * @Route("/{id}/confirm", name="confirm_publish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function confirmForPublicationAction($id)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        $subscription->publish();

        $this->get('subscription.manager')->updateSubscription($subscription);

        $this->get('mail.manager')->sendPublishedNotification($subscription);
        $this->get('mail.manager')->sendPublishedConfirmation($subscription);

        return $this->redirect($this->generateUrl('thanks_publish', array('id' => $id)));
    }

    /**
     * @Route("/{id}/thanks", name="thanks_publish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function thanksForPublicationAction($id)
    {
        return $this->render(
            'subscription/publish_thanks.html.twig',
            array(
                'subscription' => $this->getSubscription($id),
            )
        );
    }

    /**
     * @Route("/{id}/update/overview", name="overview_update")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function overviewForUpdateAction($id, Request $request)
    {
        try {
            $orgSubscription = clone $this->getSubscription($id);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        $subscription = $this->get('subscription.manager')->getSubscriptionFromSession($id, $request->getSession());

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        return $this->render(
            'subscription/update_overview.html.twig',
            array(
                'subscription'    => $subscription,
                'orgSubscription' => $orgSubscription,
            )
        );
    }

    /**
     * @Route("/{id}/update/confirm", name="confirm_update")
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function confirmForUpdateAction($id, Request $request)
    {
        try {
            $this->getSubscription($id);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        $subscription = $this->get('subscription.manager')->getSubscriptionFromSession($id, $request->getSession());

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        $this->get('subscription.manager')->updateSubscription($subscription);

        $this->get('mail.manager')->sendPublishedNotification($subscription);
        $this->get('mail.manager')->sendPublishedConfirmation($subscription);

        $this->addFlash(
            'info',
            $this->get('translator')->trans('form.status.updated')
        );

        return $this->redirect($this->generateUrl('form', array('id' => $id)));
    }

    /**
     * @Route("/{id}/finish/overview", name="overview_finish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function overviewForFinalizationAction($id)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        /** @var SubscriptionManager $subscriptionManager */
        $subscriptionManager = $this->get('subscription.manager');
        $isValid = $subscriptionManager->isValidSubscription(
            $subscription,
            array('Default', 'finalize')
        );

        if (!$isValid) {
            return $this->redirect(
                $this->generateUrl(
                    'form',
                    array('id' => $id, 'finish'=> '1')
                )
            );
        }

        return $this->render(
            'subscription/finish_overview.html.twig',
            array(
                'subscription' => $subscription,
            )
        );
    }

    /**
     * @Route("/{id}/finish/confirm", name="confirm_finish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function confirmForFinalizationAction($id)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        $subscription->finish();

        $this->get('subscription.manager')->updateSubscription($subscription);

        $this->get('mail.manager')->sendFinishedNotification($subscription);
        $this->get('mail.manager')->sendFinishedConfirmation($subscription);

        return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
    }

    /**
     * @Route("/{id}/finish/thanks", name="thanks_finish")
     *
     * @param string $id
     *
     * @return Response
     */
    public function thanksForFinalizationAction($id)
    {
        return $this->render(
            'subscription/finish_thanks.html.twig',
            array(
                'subscription' => $this->getSubscription($id, false),
            )
        );
    }

    /**
     * @param string $id
     * @param bool   $checkStatus
     * @param bool   $checkLock
     *
     * @return Subscription
     */
    private function getSubscription($id, $checkStatus = true, $checkLock = true)
    {
        $subscription = $this->get('subscription.manager')->getSubscription($id, $checkStatus, $checkLock);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        // @todo: not real nice to set the locale on the Request here...
        $this->getRequest()->setLocale($subscription->getLocale());
        $this->get('translator')->setLocale($subscription->getLocale());

        return $subscription;
    }
}
