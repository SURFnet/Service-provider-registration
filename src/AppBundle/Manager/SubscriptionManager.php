<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Subscription;
use AppBundle\Event\SubscriptionEvent;
use AppBundle\SubscriptionEvents;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Class SubscriptionManager
 */
class SubscriptionManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EntityRepository
     */
    private $repo;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LockManager;
     */
    private $lockManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor
     *
     * @param EntityManager            $entityManager
     * @param ValidatorInterface       $validator
     * @param LockManager              $lockManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator,
        LockManager $lockManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $entityManager;
        $this->repo = $entityManager->getRepository('AppBundle:Subscription');
        $this->validator = $validator;
        $this->lockManager = $lockManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $id
     * @param bool   $checkStatus
     * @param bool   $checkLock
     *
     * @return Subscription
     */
    public function getSubscription(
        $id,
        $checkStatus = false,
        $checkLock = false,
        $dispatch = true
    ) {
        if ($dispatch) {
            $this->dispatcher->dispatch(
                SubscriptionEvents::PRE_READ,
                new SubscriptionEvent($id)
            );
        }

        $subscription = $this->repo->find($id);

        if (empty($subscription)) {
            return $subscription;
        }

        if ($checkLock && !$this->lockManager->lock($id)) {
            throw new \RuntimeException('Subscription is locked');
        }

        if ($checkStatus && $subscription->isFinished()) {
            throw new \InvalidArgumentException('Subscription has already been finished');
        }

        return $subscription;
    }

    /**
     * @return Subscription[]
     */
    public function getDraftSubscriptions()
    {
        return $this->repo->findBy(array(
            'status' => Subscription::STATE_DRAFT,
            'archived' => false,
        ));
    }

    /**
     * @param Subscription $subscription
     */
    public function saveSubscription(Subscription $subscription)
    {
        $this->em->persist($subscription);
        $this->em->flush($subscription);

        $this->dispatcher->dispatch(
            SubscriptionEvents::POST_WRITE,
            new SubscriptionEvent($subscription->getId(), $subscription)
        );
    }

    /**
     * @param Subscription $subscription
     * @param bool $dispatch
     */
    public function updateSubscription(
        Subscription $subscription,
        $dispatch = true
    ) {
        $this->em->flush($subscription);

        if (!$dispatch) {
            return;
        }

        $this->dispatcher->dispatch(
            SubscriptionEvents::POST_WRITE,
            new SubscriptionEvent($subscription->getId(), $subscription)
        );
    }

    /**
     * @param Subscription $subscription
     */
    public function deleteSubscription(Subscription $subscription)
    {
        $this->em->remove($subscription);
        $this->em->flush();
    }

    /**
     * @param Subscription $subscription
     *
     * @return bool
     */
    public function isValidSubscription(Subscription $subscription)
    {
        return count($this->validator->validate($subscription)) === 0;
    }

    /**
     * Get a count for the number of subscriptions for a given status.
     *
     * @param int $status
     * @return int
     */
    public function countForType($status)
    {
        return (int) $this->em->createQueryBuilder()
            ->select('count(subscription.id)')
            ->from('AppBundle:Subscription', 'subscription')
            ->where('subscription.status = :status')
            ->andWhere('subscription.archived = :archived')
            ->setParameter('status', $status)
            ->setParameter('archived', false)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
