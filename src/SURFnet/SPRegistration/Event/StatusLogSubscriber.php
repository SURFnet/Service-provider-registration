<?php

namespace SURFnet\SPRegistration\Event;

use AppBundle\Entity\SubscriptionStatusChange;
use AppBundle\Entity\SubscriptionStatusChangeRepository;
use AppBundle\Event\SubscriptionEvent;
use AppBundle\SubscriptionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusLogSubscriber implements EventSubscriberInterface
{
    public function postWrite(SubscriptionEvent $e)
    {
        $oldStatus = null;
        $newStatus = null;
        $subscriptionId = null;

        $oldSubscription = $e->getOldSubscription();
        if ($oldSubscription) {
            $oldStatus = $oldSubscription->getStatus();
            $subscriptionId = $oldSubscription->getId();
        }

        $newSubscription = $e->getNewSubscription();
        if ($newSubscription) {
            $newStatus = $newSubscription->getStatus();
            $subscriptionId = $newSubscription->getId();
        }

        if ($oldStatus === $newStatus) {
            return;
        }

        $entity = new SubscriptionStatusChange(
            $subscriptionId,
            $oldStatus,
            $newStatus
        );

        $this->repository->save($entity);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            SubscriptionEvents::POST_WRITE => 'postWrite',
        );
    }

    /**
     * StatusLogSubscriber constructor.
     * @param SubscriptionStatusChangeRepository $repository
     */
    public function __construct(SubscriptionStatusChangeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var SubscriptionStatusChangeRepository
     */
    private $repository;
}
