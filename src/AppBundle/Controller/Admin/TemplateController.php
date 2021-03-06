<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Template;
use AppBundle\Form\Admin\TemplateType;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\Source\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TemplateController
 *
 * @Route("/admin/templates")
 */
class TemplateController extends Controller implements SecuredController
{
    /**
     * @Route("/", name="admin.template.overview")
     *
     * @return Response
     */
    public function overviewAction()
    {
        return $this->render('admin/template/overview.html.twig');
    }

    /**
     * @Route("/grid", name="admin.template.grid")
     *
     * @return Response
     */
    public function gridAction()
    {
        return $this->buildGrid()->getGridResponse('admin/template/grid.html.twig');
    }

    /**
     * @Route("/{id}/edit", name="admin.template.edit")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     */
    public function editAction($id, Request $request)
    {
        /** @var Template $template */
        $template = $this->getDoctrine()->getManager()->find('AppBundle:Template', $id);

        $form = $this->createForm(
            new TemplateType(),
            $template,
            array()
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // Force cache refresh of template
            $twig = $this->get('twig');
            $twig->enableAutoReload();
            $twig->loadTemplate($template->getName());

            return $this->redirect($this->generateUrl('admin.template.overview'));
        }

        return $this->render(
            'admin/template/edit.html.twig',
            array(
                'template' => $template,
                'form'     => $form->createView(),
            )
        );
    }

    /**
     * @return Grid
     */
    private function buildGrid()
    {
        $grid = $this->get('grid');

        $source = new Entity('AppBundle:Template');
        $grid->setSource($source);

        $rowAction = new RowAction('edit', 'admin.template.edit');
        $grid->addRowAction($rowAction);

        $grid->setId('adminTemplateGrid');
        $grid->setRouteUrl($this->generateUrl('admin.template.grid'));

        $grid->setDefaultOrder('name', 'asc');
        $grid->setLimits(array(10, 25, 50));
        $grid->setDefaultLimit(25);

        $grid->setActionsColumnTitle('');

        return $grid;
    }
}
