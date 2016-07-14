<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ProductionFinalController
 * @package AppBundle\Controller
 *
 * @Route("/production/subscription")
 */
final class ProductionFinishedController extends Controller
{
    /**
     * @Method({"GET"})
     * @Route("/{id}/finished", name="production_finished_thanks")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function thanksAction(Subscription $subscription)
    {
        return $this->render(
            ':subscription/production:finished_thanks.html.twig',
            array(
                'subscription' => $subscription,
            )
        );
    }
}
