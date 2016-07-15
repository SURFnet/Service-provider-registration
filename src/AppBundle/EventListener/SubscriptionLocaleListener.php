<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\SubscriptionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SubscriptionLocaleListener
 * @package AppBundle\EventListener
 */
final class SubscriptionLocaleListener implements EventSubscriberInterface
{
    /**
     * See if the request has a subscription attached to it, if so use it's locale in the request.
     *
     * @param GetResponseEvent $e
     *   Kernel event for getting the response from a request.
     */
    public function updateRequestLocale(GetResponseEvent $e)
    {
        $request = $e->getRequest();

        $id = $request->attributes->get('id');
        if (!$id) {
            return;
        }

        $subscription = $this->repository->findById($id);
        if (!$subscription) {
            return;
        }

        $request->setLocale($subscription->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('updateRequestLocale', 17),
        );
    }

    /**
     * SubscriptionLocaleListener constructor.
     * @param SubscriptionRepository $repository
     */
    public function __construct(SubscriptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @var SubscriptionRepository
     */
    private $repository;
}
