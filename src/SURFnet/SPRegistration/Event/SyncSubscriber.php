<?php

namespace SURFnet\SPRegistration\Event;

use AppBundle\Entity\Subscription;
use AppBundle\Manager\SubscriptionManager;
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
    /**
     * @param Subscription $request
     */
    public function sync(GetResponseEvent $e)
    {
        if ($e->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        $request = $this->subscriptionRepository->getSubscription(
            '2f56edb4-6036-11e5-a860-08002708c2e4'
        );
        $this->service->sync($request);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // @todo use real event names.
            'pre-load' => 'sync',
            'post-save' => 'sync',
            KernelEvents::REQUEST => 'sync',
        );
    }

    /**
     * SyncSubscriber constructor.
     * @param JanusSyncService $service
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
