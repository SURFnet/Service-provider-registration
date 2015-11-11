<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Subscription;
use AppBundle\Entity\SubscriptionStatusChangeRepository;
use DateTime;
use DateTimeZone;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class IndexController extends Controller implements SecuredController
{
    /**
     * @Route("/", name="admin")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/status/current.json", name="status_current")
     *
     * @return Response
     */
    public function getStatusCurrentAction()
    {
        $repository = $this->get('subscription.manager');
        return new JsonResponse(array(
            'draft'     => $repository->countForType(Subscription::STATE_DRAFT),
            'published' => $repository->countForType(Subscription::STATE_PUBLISHED),
            'finished'  => $repository->countForType(Subscription::STATE_FINISHED)
        ));
    }

    /**
     * @Route("/status/history.json", name="status_history")
     *
     * @return Response
     */
    public function getStatusHistoryAction(Request $request)
    {
        $from = new DateTime($request->get('from'));
        $to   = new DateTime($request->get('to'));

        /** @var SubscriptionStatusChangeRepository $repository */
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:SubscriptionStatusChange');

        return new JsonResponse(
            array(
                'meta' => array(
                    'from' => $from->format('c'),
                    'to' => $to->format('c'),
                ),
                'data' => $repository->countByDateRange($from, $to)
            )
        );
    }
}
