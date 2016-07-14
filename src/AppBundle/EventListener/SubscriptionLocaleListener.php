<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Subscription;
use Composer\EventDispatcher\EventSubscriberInterface;
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

        $subscription = $request->attributes->get('subscription');
        if ($subscription) {
            return;
        }

        if (!$subscription instanceof Subscription) {
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
}
