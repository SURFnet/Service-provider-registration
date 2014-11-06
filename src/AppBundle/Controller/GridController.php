<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use APY\DataGridBundle\Grid\Source\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GridController
 */
class GridController extends Controller
{
    /**
     * @Route("/grid", name="grid")
     *
     * @return Response
     */
    public function gridAction()
    {
        $source = new Entity('AppBundle:Contact');

        $grid = $this->get('grid');
        $grid->setSource($source);

        return $grid->getGridResponse('grid/grid.html.twig');
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction()
    {
        $entity = new Contact();
        $entity->setName(rand(0, 100));
        $entity->setCity(rand(0, 100));
        $entity->setRemote(rand(0, 100));
        $entity->setComment(rand(0, 100));

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    }
}
