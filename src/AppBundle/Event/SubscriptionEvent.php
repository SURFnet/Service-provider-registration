<?php

namespace AppBundle\Event;

use AppBundle\Entity\Subscription;

/**
 * Class SubscriptionEvent
 */
class SubscriptionEvent
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
