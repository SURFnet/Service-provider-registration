<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Form\ContactType;
use Doctrine\Common\Cache\ApcCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FormController
 */
class FormController extends Controller
{
    /**
     * @Route("/{id}", requirements={"id" = "\d+"}, name="form")
     * @Method("GET")
     *
     * @param string $id
     *
     * @return Response
     */
    public function formAction($id)
    {
        try {
            $subscription = $this->getSubscription($id);
        } catch (\InvalidArgumentException $e) {
            return $this->redirect($this->generateUrl('thanks', array('id' => $id)));
        }

        $form = $this->getForm($subscription);

        return $this->render(
            'form/form.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
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
            $this->saveSubscription($subscription);
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

        if (!$form->isValid()) {
            return new Response($form->getErrors(true, false), 400);
        }

        return new Response();
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
        if (!$this->getLock($id)) {
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
            $this->saveSubscription($subscription);
        }

        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('overview', array('id' => $id)));
        }

        return $this->render(
            'form/form.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
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

        return $this->render(
            'form/overview.html.twig',
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

        $subscription->finish();

        $this->saveSubscription($subscription);

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
            'form/thanks.html.twig',
            array(
                'subscription' => $this->getSubscription($id, false),
            )
        );
    }

    /**
     * @param string $id
     *
     * @return bool
     * @todo: this is not atomic!
     */
    private function getLock($id)
    {
        /** @var ApcCache $cache */
        $cache = $this->get('cache');
        $session = $this->get('session');

        $cacheId = 'lock-' . $id;
        $sessionId = $session->getId();

        $lock = $cache->fetch($cacheId);

        // If there already is a lock for another session -> fail.
        if ($lock !== false && $lock !== $sessionId) {
            return false;
        }

        return $cache->save($cacheId, $sessionId, 12);
    }

    /**
     * @param Contact $contact
     * @param bool    $useCsrf
     *
     * @return Form
     */
    private function getForm(Contact $contact, $useCsrf = true)
    {
        $form = $this->createForm(
            new ContactType(),
            $contact,
            array(
                'disabled'        => !$this->getLock($contact->getId()),
                'csrf_protection' => $useCsrf
            )
        );

        return $form;
    }

    /**
     * @param string $id
     *
     * @return Contact
     * @todo: move to 'manager'
     */
    private function getSubscription($id, $checkStatus = true)
    {
        $subscription = $this->getDoctrine()->getRepository('AppBundle:Contact')->find($id);

        if (empty($subscription)) {
            throw $this->createNotFoundException();
        }

        if ($checkStatus && $subscription->isFinished()) {
            throw new \InvalidArgumentException('Subscription has already been finished');
        }

        return $subscription;
    }

    /**
     * @param Contact $subscription
     * @todo: move to 'manager'
     */
    private function saveSubscription(Contact $subscription)
    {
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
}
