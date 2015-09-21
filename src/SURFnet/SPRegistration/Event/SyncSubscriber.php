<?php

namespace SURFnet\SPRegistration\Event;

use AppBundle\Entity\Subscription;
use SURFnet\SPRegistration\Service\JanusSyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SyncSubscriber
 * @package SURFnet\SPRegistration\Event
 */
class SyncSubscriber implements EventSubscriberInterface
{
    /**
     * @param Subscription $request
     */
    public function sync(Subscription $request)
    {
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
        );
    }

    /**
     * SyncSubscriber constructor.
     * @param JanusSyncService $service
     */
    public function __construct(JanusSyncService $service)
    {
        $this->service = $service;
    }

    /**
     * @var JanusSyncService
     */
    private $service;
}
