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
     * @param string $id
     *
     * @return Response
     */
    public function formAction($id)
    {
        try {
            $subscription = $this->getSubscription($id, true, false);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks', array('id' => $id)));
        }

        $form = $this->getForm($subscription);

        return $this->render(
            'subscription/form.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
                'locked' => !$this->get('lock.manager')->getLock($id)
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

        $form = $this->getForm($subscription);

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
     */
    public function validateAction($id, Request $request)
    {
        $subscription = $this->getSubscription($id);

        $form = $this->getForm($subscription, false);

        $form->submit($request->get($form->getName()), false);

        $response = array('data' => array(), 'errors' => array());

        // @todo: use recursive method
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
        if (!$this->get('lock.manager')->getLock($id)) {
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
    public function finishAction($id, Request $request)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks', array('id' => $id)));
        }

        $form = $this->getForm($subscription);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->get('subscription.manager')->updateSubscription($subscription);
        }

        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('overview', array('id' => $id)));
        }

        return $this->render(
            'subscription/form.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
                'locked' => !$this->get('lock.manager')->getLock($id)
            )
        );
    }

    /**
     * @Route("/{id}/overview", name="overview")
     *
     * @param string $id
     *
     * @return Response
     */
    public function overviewAction($id)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks', array('id' => $id)));
        }

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        return $this->render(
            'subscription/overview.html.twig',
            array(
                'subscription' => $subscription
            )
        );
    }

    /**
     * @Route("/{id}/confirm", name="confirm")
     *
     * @param string $id
     *
     * @return Response
     */
    public function confirmAction($id)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks', array('id' => $id)));
        }

        if (!$this->get('subscription.manager')->isValidSubscription($subscription)) {
            return $this->redirect($this->generateUrl('form', array('id' => $id)));
        }

        $subscription->finish();

        $this->get('subscription.manager')->updateSubscription($subscription);

        return $this->redirect($this->generateUrl('thanks', array('id' => $id)));
    }

    /**
     * @Route("/{id}/thanks", name="thanks")
     *
     * @param string $id
     *
     * @return Response
     */
    public function thanksAction($id)
    {
        return $this->render(
            'subscription/thanks.html.twig',
            array(
                'subscription' => $this->getSubscription($id, false),
            )
        );
    }

    /**
     * @param Subscription $subscription
     * @param bool         $useCsrf
     *
     * @return Form
     */
    private function getForm(Subscription $subscription, $useCsrf = true)
    {
        $form = $this->createForm(
            new SubscriptionType($this->get('parser')),
            $subscription,
            array(
                'disabled' => !$this->get('lock.manager')->getLock($subscription->getId()),
                'csrf_protection' => $useCsrf
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

        return $subscription;
    }
}
