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
 * Class ProductionFinalizeController
 * @package AppBundle\Controller
 *
 * @Route("/production/subscription")
 */
final class ProductionFinishController extends Controller
{
    /**
     * @Method({"GET","POST"})
     * @Route("/{id}/finish", name="production_finish")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function finishAction(Subscription $subscription, Request $request)
    {
        SubscriptionValidator::create($subscription)
            ->isForEnvironment(Subscription::ENVIRONMENT_PRODUCTION)
            ->isOfStatus(Subscription::STATE_PUBLISHED);

        if (!$this->get('lock.manager')->lock($subscription->getId())) {
            throw new BadRequestHttpException('Subscription is locked to another session');
        }

        if (count($this->get('validator')->validate($subscription, array('Default', 'finalize'))) > 0) {
            return $this->redirectToRoute(
                'production_draft_edit',
                array('id' => $subscription->getId())
            );
        }

        if (!$request->isMethod(Request::METHOD_POST)) {
            return $this->render(
                ':subscription/production:finish_overview.html.twig',
                array(
                    'subscription' => $subscription,
                )
            );
        }

        $originalSubscription = clone $subscription;
        $subscription->finish();

        $this->get('subscription.repository')->update(
            $originalSubscription,
            $subscription
        );

        $mailManager = $this->get('mail.manager');
        $mailManager->sendFinishedNotification($subscription);
        $mailManager->sendFinishedConfirmation($subscription);

        return $this->redirectToRoute(
            'production_finished_thanks',
            array('id' => $subscription->getId())
        );
    }
}
