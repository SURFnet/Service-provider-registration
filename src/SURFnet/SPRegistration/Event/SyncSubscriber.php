<?php

namespace SURFnet\SPRegistration\Event;

use AppBundle\Entity\Subscription;
use AppBundle\Event\SubscriptionEvent;
use AppBundle\Manager\SubscriptionManager;
use AppBundle\SubscriptionEvents;
use SURFnet\SPRegistration\Service\JanusSyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SyncSubscriber
 * @package SURFnet\SPRegistration\Event
 */
class SyncSubscriber implements EventSubscriberInterface
{
    private $syncing = false;

    /**
     * @param SubscriptionEvent $e
     */
    public function sync(SubscriptionEvent $e)
    {
        if ($this->syncing) {
            return;
        }
        $this->syncing = true;

        $this->doSync($e->getSubscriptionId(), $e->getSubscription());

        $this->syncing = false;
    }

    /**
     * @param string $subscriptionId
     * @param Subscription|null $subscription
     */
    private function doSync($subscriptionId, Subscription $subscription = null)
    {
        if (!$subscription) {
            $subscription = $this->subscriptionRepository->getSubscription(
                $subscriptionId,
                false,
                false
            );
        }

        if (!$subscription) {
            return;
        }

        $this->service->sync($subscription);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            SubscriptionEvents::PRE_READ    => 'sync',
            SubscriptionEvents::POST_WRITE  => 'sync',
        );
    }

    /**
     * SyncSubscriber constructor.
     * @param JanusSyncService $service
     * @param SubscriptionManager $subscriptionRepository
     */
    public function __construct(
        JanusSyncService $service,
        SubscriptionManager $subscriptionRepository
    ) {
        $this->service = $service;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @var JanusSyncService
     */
    private $service;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionRepository;
}