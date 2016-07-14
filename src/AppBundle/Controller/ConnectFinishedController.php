<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ConnectFinalController
 * @package AppBundle\Controller
 *
 * @Route("/connect/subscription")
 */
final class ConnectFinishedController extends Controller
{
    /**
     * @Method({"GET"})
     * @Route("/{id}/finished", name="connect_finished_thanks")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function thanksAction(Subscription $subscription)
    {
        return $this->render(
            ':subscription/connect:finished_thanks.html.twig',
            array(
                'subscription' => $subscription,
            )
        );
    }
}
