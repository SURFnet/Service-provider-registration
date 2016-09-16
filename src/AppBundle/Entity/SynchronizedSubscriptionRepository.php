<?php

namespace AppBundle\Entity;

use SURFnet\SPRegistration\Service\JanusSyncService;

/**
 * Class SynchronizedSubscriptionRepository
 * @package AppBundle\Entity
 */
class SynchronizedSubscriptionRepository implements SubscriptionRepository
{
    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $subscription = $this->doctrineRepository->findById($id);

        if (!$subscription) {
            return null;
        }

        if (!$this->shouldBeSynced($subscription)) {
            return $subscription;
        }

        return $this->syncService->pull($subscription);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Subscription $newSubscription)
    {
        $this->doctrineRepository->insert($newSubscription);

        if (!$this->shouldBeSynced($newSubscription)) {
            return $newSubscription;
        }

        return $this->syncService->push($newSubscription);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Subscription $oldSubscription, Subscription $newSubscription)
    {
        $this->doctrineRepository->update($oldSubscription, $newSubscription);

        if (!$this->shouldBeSynced($newSubscription)) {
            return $newSubscription;
        }

        return $this->syncService->push($newSubscription);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Subscription $newSubscription)
    {
        $this->doctrineRepository->delete($newSubscription);
    }

    /**
     * @param Subscription $subscription
     * @return bool
     */
    public function shouldBeSynced(Subscription $subscription)
    {
        if ($subscription->getEnvironment() !== Subscription::ENVIRONMENT_CONNECT) {
            return false;
        }

        if ($subscription->getStatus() !== Subscription::STATE_PUBLISHED) {
            return false;
        }

        return true;
    }

    /**
     * SynchronizedSubscriptionRepository constructor.
     * @param DoctrineSubscriptionRepository $doctrineRepository
     * @param JanusSyncService $syncService
     */
    public function __construct(
        DoctrineSubscriptionRepository $doctrineRepository,
        JanusSyncService $syncService
    ) {
        $this->doctrineRepository = $doctrineRepository;
        $this->syncService = $syncService;
    }

    /**
     * @var DoctrineSubscriptionRepository
     */
    private $doctrineRepository;

    /**
     * @var JanusSyncService
     */
    private $syncService;
}
