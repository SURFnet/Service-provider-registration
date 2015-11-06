<?php

namespace SURFnet\SPRegistration\Event;

use AppBundle\Event\SubscriptionEvent;
use AppBundle\Manager\SubscriptionManager;
use AppBundle\SubscriptionEvents;
use SURFnet\SPRegistration\Service\JanusSyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SyncSubscriber
 *
 * @package SURFnet\SPRegistration\Event
 */
class SyncSubscriber implements EventSubscriberInterface
{
    /**
     * @param SubscriptionEvent $e
     */
    public function pull(SubscriptionEvent $e)
    {
        $subscriptionRepository = $this->subscriptionRepository;
        $service = $this->service;
        $this->whileNotSyncing(
            function () use ($subscriptionRepository, $service, $e) {
                $subscriptionId = $e->getSubscriptionId();

                $subscription = $subscriptionRepository->getSubscription(
                    $subscriptionId,
                    false,
                    false
                );

                if (!$subscription) {
                    return;
                }

                $service->pull($subscription);
            }
        );
    }

    /**
     * @param SubscriptionEvent $e
     */
    public function push(SubscriptionEvent $e)
    {
        $service = $this->service;
        $this->whileNotSyncing(
            function () use ($service, $e) {
                $subscription = $e->getNewSubscription();

                if (!$subscription) {
                    return;
                }

                $service->push($subscription);
            }
        );
    }

    /**
     * @param $fn
     */
    public function whileNotSyncing($fn)
    {
        if ($this->syncing) {
            return;
        }
        $this->syncing = true;

        $fn();

        $this->syncing = false;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            SubscriptionEvents::PRE_READ   => 'pull',
            SubscriptionEvents::POST_WRITE => 'push',
        );
    }

    /**
     * SyncSubscriber constructor.
     *
     * @param JanusSyncService    $service
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

    /**
     * @var bool
     */
    private $syncing = false;
}
