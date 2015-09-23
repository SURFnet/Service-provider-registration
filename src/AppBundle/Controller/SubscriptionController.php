<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Form\SubscriptionType;
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
    public function formAction($id, Request $request)
    {
        try {
            $subscription = $this->getSubscription($id, true, false);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        $form = $this->getForm($subscription, $request->getSession());

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
            throw new \InvalidArgumentException('(auto)save is only allowed for drafts');
        }

        $form = $this->getForm($subscription, $request->getSession());

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

        $form = $this->getForm($subscription, $request->getSession(), false);

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
    public function storeAction($id, Request $request)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        $form = $this->getForm($subscription, $request->getSession());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($subscription->isPublished()) {
                $this->storeSubscriptionInSession($subscription, $request->getSession());

                return $this->redirect($this->generateUrl('overview_update', array('id' => $id)));
            }

            $this->get('subscription.manager')->updateSubscription($subscription);

            return $this->redirect($this->generateUrl('overview_publish', array('id' => $id)));
        }

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
        } catch (\InvalidArgumentException $e) {
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
        } catch (\InvalidArgumentException $e) {
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
            $this->getSubscription($id);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        $subscription = $this->getSubscriptionFromSession($id, $request->getSession(), false);

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        return $this->render(
            'subscription/update_overview.html.twig',
            array(
                'subscription' => $subscription,
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
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        $subscription = $this->getSubscriptionFromSession($id, $request->getSession());

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
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks_finish', array('id' => $id)));
        }

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
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
        } catch (\InvalidArgumentException $e) {
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
     * @param Subscription     $subscription
     * @param SessionInterface $session
     * @param bool             $useCsrf
     *
     * @return Form
     */
    private function getForm(Subscription $subscription, SessionInterface $session, $useCsrf = true)
    {
        $form = $this->createForm(
            new SubscriptionType($this->get('parser'), $session),
            $subscription,
            array(
                'disabled'        => !$this->get('lock.manager')->lock($subscription->getId()),
                'csrf_protection' => $useCsrf,
            )
        );

        return $form;
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

    /**
     * @param string           $id
     * @param SessionInterface $session
     * @param bool             $clearSession
     *
     * @return Subscription
     */
    private function getSubscriptionFromSession($id, SessionInterface $session, $clearSession = true)
    {
        $sessionId = 'subscription-' . $id;

        $subscription = $session->get($sessionId);

        if ($clearSession) {
            $session->set($sessionId, null);
        }

        if (!$subscription instanceof Subscription) {
            throw $this->createNotFoundException('Subscription not found in session');
        }

        return $this->get('subscription.manager')->merge($subscription);
    }

    /**
     * @param Subscription     $subscription
     * @param SessionInterface $session
     */
    private function storeSubscriptionInSession(Subscription $subscription, SessionInterface $session)
    {
        $sessionId = 'subscription-' . $subscription->getId();

        $this->get('subscription.manager')->detach($subscription);

        $session->set($sessionId, $subscription);
    }
}
