<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\Admin\SecuredController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class SimpleSAMLAuthListener
 */
class SimpleSAMLAuthListener
{
    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        // $controller can be either a class or a Closure.
        if (!is_array($controller)) {
            return;
        }

        if (!$controller[0] instanceof SecuredController) {
            return;
        }

        $as = new \SimpleSAML_Auth_Simple('default-sp');
        $as->requireAuth();

        if (!$as->isAuthenticated()) {
            throw new AccessDeniedHttpException('This action needs a valid login!');
        }
    }
}
