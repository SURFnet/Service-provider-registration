<?php

namespace AppBundle\Entity;

use AppBundle\Event\SubscriptionEvent;
use AppBundle\SubscriptionEvents;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class DoctrineSubscriptionRepository
 * @package AppBundle\Entity
 */
final class DoctrineSubscriptionRepository extends EntityRepository implements SubscriptionRepository
{
    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        return $this->find($id);
    }

    /**
     * @return Subscription[]
     */
    public function findDraftSubscriptions()
    {
        return $this->findBy(array(
            'status' => Subscription::STATE_DRAFT,
            'archived' => false,
        ));
    }
    /**
     * Get a count for the number of subscriptions for a given status.
     *
     * @param int $status
     * @return int
     */
    public function countForType($status)
    {
        return (int) $this->_em->createQueryBuilder()
            ->select('count(subscription.id)')
            ->from('AppBundle:Subscription', 'subscription')
            ->where('subscription.status = :status')
            ->andWhere('subscription.archived = :archived')
            ->setParameter('status', $status)
            ->setParameter('archived', false)
            ->getQuery()
            ->getSingleScalarResult();
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
        $this->save($newSubscription, $oldSubscription);
        return $newSubscription;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Subscription $subscription)
    {
        $this->_em->remove($subscription);
        $this->_em->flush($subscription);
    }

    /**
     * Save a subscription.
     *
     * @param Subscription $subscription
     */
    private function save(Subscription $subscription, Subscription $oldSubscription = null)
    {
        $this->_em->persist($subscription);
        $this->_em->flush($subscription);

        $this->dispatcher->dispatch(
            SubscriptionEvents::POST_WRITE,
            new SubscriptionEvent(
                $subscription->getId(),
                $oldSubscription,
                $subscription
            )
        );
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
}
