<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SessionSubscriptionRepository
 * @package AppBundle\Entity
 */
class SessionSubscriptionRepository implements SubscriptionRepository
{
    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $sessionId = 'subscription-' . $id;

        $subscription = $this->session->get($sessionId);

        if (!$subscription instanceof Subscription) {
            return null;
        }

        return $this->em->merge($subscription);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Subscription $newSubscription)
    {
        $this->save($newSubscription);
        return $newSubscription;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Subscription $oldSubscription, Subscription $newSubscription)
    {
        $this->save($newSubscription);
        return $newSubscription;
    }

    /**
     * @param Subscription $subscription
     */
    private function save(Subscription $subscription)
    {
        $sessionId = 'subscription-' . $subscription->getId();

        $this->em->detach($subscription);

        $this->session->set($sessionId, $subscription);
    }

    /**
     * SessionSubscriptionRepository constructor.
     * @param Session $session
     * @param EntityManager $em
     */
    public function __construct(Session $session, EntityManager $em)
    {
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $em;
}
