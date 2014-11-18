<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GridController
 *
 * @Route("/admin")
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="admin")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('admin.subscription.overview'));
    }
}
