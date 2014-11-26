<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SimpleSAML_Auth_Simple;
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
        $as = new SimpleSAML_Auth_Simple('default-sp');
        $as->requireAuth();

        die(var_dump($as->getAttributes()));

        return $this->redirect($this->generateUrl('admin.subscription.overview'));
    }
}
