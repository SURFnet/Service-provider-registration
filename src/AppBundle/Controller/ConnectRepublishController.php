<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Validator\SubscriptionValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * Class ConnectRepublishController
 * @package AppBundle\Controller
 *
 * @Route("/connect/subscription")
 */
final class ConnectRepublishController extends Controller
{
    /**
     * @Method({"GET","POST"})
     * @Route("/{id}/republish", name="connect_republish")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function republishAction(Subscription $subscription, Request $request)
    {
        SubscriptionValidator::create($subscription)
            ->isForEnvironment(Subscription::ENVIRONMENT_CONNECT)
            ->isOfStatus(Subscription::STATE_PUBLISHED);

        if (!$this->get('lock.manager')->lock($subscription->getId())) {
            throw new BadRequestHttpException('Subscription is locked to another session');
        }

        $newSubscription = $this->get('subscription.repository.session')
            ->findById($subscription->getId());

        if (!$newSubscription) {
            throw new BadRequestHttpException('Missing subscription in session');
        }

        if (count($this->get('validator')->validate($subscription)) > 0) {
            return $this->redirectToRoute(
                'connect_published_edit',
                array('id' => $subscription->getId())
            );
        }

        if (!$request->isMethod(Request::METHOD_POST)) {
            return $this->render(
                ':subscription/connect:republish_overview.html.twig',
                array(
                    'subscription'    => $newSubscription,
                    'orgSubscription' => $subscription,
                )
            );
        }

        $this->get('subscription.repository')->update($subscription, $newSubscription);

        $mailManager = $this->get('mail.manager');
        $mailManager->sendUpdatedNotification($subscription);
        $mailManager->sendUpdatedConfirmation($subscription);

        $this->addFlash(
            'info',
            $this->get('translator')->trans('form.status.updated')
        );
        return $this->redirectToRoute('connect_published_edit', array('id' => $subscription->getId()));
    }
}
