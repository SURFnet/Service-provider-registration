<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Form\SubscriptionType;
use AppBundle\Validator\SubscriptionValidator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ConnectDraftController
 * @package AppBundle\Controller
 *
 * @Route("/connect/subscription")
 */
final class ConnectDraftController extends Controller
{
    /**
     * @Method({"GET","POST"})
     * @Route("/{id}/draft", name="connect_draft_edit")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function editAction(Subscription $subscription, Request $request)
    {
        SubscriptionValidator::create($subscription)
            ->isForEnvironment(Subscription::ENVIRONMENT_CONNECT)
            ->isOfStatus(Subscription::STATE_DRAFT);

        $originalSubscription = clone $subscription;

        $form = $this->get('subscription.form.factory')->buildForm($subscription, $request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->get('lock.manager')->lock($subscription->getId())) {
                throw new BadRequestHttpException('Subscription is locked to another session');
            }

            $this->get('subscription.repository')->update(
                $originalSubscription,
                $subscription
            );

            $requestedState = $request->get('subscription[requestedState]', null, true);
            if ($requestedState === SubscriptionType::REQUESTED_STATE_PUBLISHED) {
                return $this->redirectToRoute(
                    'connect_publish',
                    array('id' => $subscription->getId())
                );
            }
        }

        return $this->render(
            ':subscription/connect:draft_edit.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
                'locked'       => !$this->get('lock.manager')->lock($subscription->getId()),
            )
        );
    }

    /**
     * @Method({"POST"})
     * @Route("/{id}/draft/save", name="connect_draft_save")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function saveAction(Subscription $subscription, Request $request)
    {
        SubscriptionValidator::create($subscription)
            ->isForEnvironment(Subscription::ENVIRONMENT_CONNECT)
            ->isOfStatus(Subscription::STATE_DRAFT);

        if (!$this->get('lock.manager')->lock($subscription->getId())) {
            throw new BadRequestHttpException('Subscription is locked to another session');
        }

        $originalSubscription = clone $subscription;

        $form = $this->get('subscription.form.factory')->buildForm($subscription, $request);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new Response();
        }

        $this->get('subscription.repository')->update(
            $originalSubscription,
            $subscription
        );

        return new Response();
    }
}
