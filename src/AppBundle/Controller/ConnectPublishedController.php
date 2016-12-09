<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Form\SubscriptionType;
use AppBundle\Validator\SubscriptionValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConnectPublishedController
 * @package AppBundle\Controller
 *
 * @Route("/connect/subscription")
 */
final class ConnectPublishedController extends Controller
{
    /**
     * @Method({"GET", "POST"})
     * @Route("/{id}/published", name="connect_published_edit")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function editAction(Subscription $subscription, Request $request)
    {
        SubscriptionValidator::create($subscription)
            ->isForEnvironment(Subscription::ENVIRONMENT_CONNECT)
            ->isOfStatus(Subscription::STATE_PUBLISHED);

        $form = $this->get('subscription.form.factory')->buildForm($subscription, $request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If we're moving from published to finished, simply redirect.
            $requestedState = $request->get('subscription[requestedState]', null, true);
            if ($requestedState === SubscriptionType::REQUESTED_STATE_FINISHED) {
                return $this->redirect(
                    $this->generateUrl(
                        'connect_finish',
                        array('id' => $subscription->getId())
                    )
                );
            }

            $this->get('subscription.repository.session')->insert($subscription);

            if ($requestedState === SubscriptionType::REQUESTED_STATE_PUBLISHED) {
                return $this->redirectToRoute(
                    'connect_republish',
                    array('id' => $subscription->getId())
                );
            }
        }

        return $this->render(
            ':subscription/connect:published_edit.html.twig',
            array(
                'subscription' => $subscription,
                'form'         => $form->createView(),
                'locked'       => !$this->get('lock.manager')->lock($subscription->getId()),
            )
        );
    }

    /**
     * @Method({"GET"})
     * @Route("/{id}/published/thanks", name="connect_published_thanks")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function thanksAction(Subscription $subscription)
    {
        SubscriptionValidator::create($subscription)
            ->isForEnvironment(Subscription::ENVIRONMENT_CONNECT)
            ->isOfStatus(Subscription::STATE_PUBLISHED);

        return $this->render(
            ':subscription/connect:published_thanks.html.twig',
            array(
                'subscription' => $subscription,
            )
        );
    }
}
