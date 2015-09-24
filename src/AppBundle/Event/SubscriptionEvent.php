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
    private $subscription;

    /**
     * @param int          $subscriptionId
     * @param Subscription $subscription
     */
    public function __construct($subscriptionId, Subscription $subscription = null)
    {
        $this->subscriptionId = $subscriptionId;
        $this->subscription = $subscription;
    }

    /**
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @return Subscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }
}
