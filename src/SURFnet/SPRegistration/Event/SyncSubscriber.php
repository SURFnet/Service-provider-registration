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
     * @var JanusSyncService
     */
    private $service;

    /**
     * SyncSubscriber constructor.
     * @param JanusSyncService $service
     */
    public function __construct(JanusSyncService $service)
    {
        $this->service = $service;
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
     * @param Subscription $request
     */
    public function sync(Subscription $request)
    {
        $this->service->sync($request);
    }
}
