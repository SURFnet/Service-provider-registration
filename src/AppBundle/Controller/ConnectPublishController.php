<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Validator\SubscriptionValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ConnectPublishController
 * @package AppBundle\Controller
 *
 * @Route("/connect/subscription")
 */
final class ConnectPublishController extends Controller
{
    /**
     * @Method({"GET","POST"})
     * @Route("/{id}/publish", name="connect_publish")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function publishAction(Subscription $subscription, Request $request)
    {
        SubscriptionValidator::create($subscription)
            ->isForEnvironment(Subscription::ENVIRONMENT_CONNECT)
            ->isOfStatus(Subscription::STATE_DRAFT);

        if (!$this->get('lock.manager')->lock($subscription->getId())) {
            throw new BadRequestHttpException('Subscription is locked to another session');
        }

        if (count($this->get('validator')->validate($subscription)) > 0) {
            return $this->redirectToRoute(
                'connect_draft_edit',
                array('id' => $subscription->getId())
            );
        }

        if (!$request->isMethod(Request::METHOD_POST)) {
            return $this->render(
                ':subscription/connect:publish_overview.html.twig',
                array(
                    'subscription' => $subscription,
                )
            );
        }

        $fromSubscription = clone $subscription;

        $subscription->publish();

        $this->get('subscription.repository')->update(
            $fromSubscription,
            $subscription
        );

        $mailManager = $this->get('mail.manager');
        $mailManager->sendPublishedNotification($subscription);
        $mailManager->sendPublishedConfirmation($subscription);

        return $this->redirectToRoute(
            'connect_published_thanks',
            array('id' => $subscription->getId())
        );
    }
}
