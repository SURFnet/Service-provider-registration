<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Subscription;
use Ob\HighchartsBundle\Highcharts\ChartOption;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GridController
 *
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
        $repository = $this->get('subscription.manager');

        // Chart
        $series = array(
            array(
                'name' => 'Draft',
                'data' => array(
                    $repository->countForType(Subscription::STATE_DRAFT)
                ),
                'type'  => 'column',
            ),
            array(
                'name' => 'Published',
                'data' => array(
                    $repository->countForType(Subscription::STATE_PUBLISHED)
                ),
                'type'  => 'column',
            ),
            array(
                'name' => 'Final',
                'data' => array(
                    $repository->countForType(Subscription::STATE_FINISHED)
                ),
                'type'  => 'column',
            ),
        );

        $ob = new Highchart();
        $ob->chart->renderTo('per-status-chart');  // The #id of the div where to render the chart
        $ob->chart->options3d(array(
            'enabled' => true,
            'alpha' => 15,
            'beta' => 15,
            'viewDistance' => 25,
        ));
        $ob->title->text('Registrations per status');
        $ob->xAxis->title(array('text'  => "Status"));
        $ob->yAxis->allowDecimals(false);
        $ob->yAxis->title(array('text'  => "Number of registrations"));
        $ob->series($series);

        return $this->render(
            'admin/index.html.twig',
            array(
                'chart' => $ob,
            )
        );
    }
}
