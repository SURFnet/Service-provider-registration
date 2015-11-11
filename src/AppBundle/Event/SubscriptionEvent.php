<?php

namespace AppBundle\Event;

use AppBundle\Entity\Subscription;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SubscriptionEvent
 */
class SubscriptionEvent extends Event
{
    /**
     * @var int
     */
    private $subscriptionId;

    /**
     * @var Subscription
     */
    private $oldSubscription;

    /**
     * @var Subscription
     */
    private $newSubscription;

    /**
     * @param int          $subscriptionId
     * @param Subscription $oldSubscription
     * @param Subscription $newSubscription
     */
    public function __construct(
        $subscriptionId,
        Subscription $oldSubscription = null,
        Subscription $newSubscription = null
    ) {
        $this->subscriptionId = $subscriptionId;
        $this->oldSubscription = $oldSubscription;
        $this->newSubscription = $newSubscription;
    }

    /**
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @return Subscription|null
     */
    public function getOldSubscription()
    {
        return $this->oldSubscription;
    }

    /**
     * @return Subscription|null
     */
    public function getNewSubscription()
    {
        return $this->newSubscription;
    }
}
