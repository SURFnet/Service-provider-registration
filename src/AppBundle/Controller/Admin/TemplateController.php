<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TemplateController
 *
 * @Route("/admin/templates")
 */
class TemplateController extends Controller
{
    /**
     * @Route("/", name="admin.template.overview")
     *
     * @return Response
     */
    public function overviewAction()
    {
        return new Response();
    }
}
