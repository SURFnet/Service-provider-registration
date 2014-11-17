<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Model\Contact;
use APY\DataGridBundle\Grid\Action\RowAction;
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
    public function overviewAction()
    {
        $source = new Entity('AppBundle:Subscription');

        $grid = $this->get('grid');
        $grid->setSource($source);

        $grid->addRowAction(new RowAction('view', 'form', false, '_blank'));

        return $grid->getGridResponse('grid/overview.html.twig');
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction()
    {
        $entity = new Subscription();
        $entity->setTicketNo(rand(0, 100));
        $entity->setLocale('nl');

        $contact = new Contact();
        $contact->setEmail('test@domain.org');
        $entity->setContact($contact);

        $entity->setComments(rand(0, 100));

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('grid'));
    }
}
