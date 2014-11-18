<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TextController
 *
 * @Route("/admin/texts")
 */
class TextController extends Controller
{
    /**
     * @Route("/", name="admin.text.overview")
     *
     * @return Response
     */
    public function overviewAction()
    {
        return new Response();
    }
}
