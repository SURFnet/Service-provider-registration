<?php

namespace AppBundle\Entity;

/**
 * Interface SubscriptionRepository
 * @package AppBundle\Entity
 */
interface SubscriptionRepository
{
    /**
     * @param string $id
     * @return Subscription|null
     */
    public function findById($id);

    /**
     * @param Subscription $newSubscription
     * @return Subscription
     */
    public function insert(Subscription $newSubscription);

    /**
     * @param Subscription $oldSubscription
     * @param Subscription $newSubscription
     * @return Subscription
     */
    public function update(Subscription $oldSubscription, Subscription $newSubscription);
}
