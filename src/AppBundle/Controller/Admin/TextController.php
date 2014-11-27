<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Form\Admin\TextType;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\Source\Entity;
use Lexik\Bundle\TranslationBundle\Entity\Translation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TextController
 *
 * @Route("/admin/texts")
 */
class TextController extends Controller implements SecuredController
{
    /**
     * @Route("/", name="admin.text.overview")
     *
     * @return Response
     */
    public function overviewAction()
    {
        return $this->render('admin/text/overview.html.twig');
    }

    /**
     * @Route("/grid", name="admin.text.grid")
     *
     * @return Response
     */
    public function gridAction()
    {
        return $this->buildGrid()->getGridResponse('admin/text/grid.html.twig');
    }

    /**
     * @Route("/{id}/edit", name="admin.text.edit")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     */
    public function editAction($id, Request $request)
    {
        /** @var \Lexik\Bundle\TranslationBundle\Entity\TransUnit $text */
        $text = $this->getDoctrine()->getManager()->find('LexikTranslationBundle:TransUnit', $id);

        foreach (array('nl', 'en') as $locale) {
            if (!$text->hasTranslation($locale)) {
                $translation = new Translation();
                $translation->setLocale($locale);

                $text->addTranslation($translation);
            }
        }

        $handler = $this->get('lexik_translation.form.handler.trans_unit');

        $form = $this->createForm(new TextType(), $text, $handler->getFormOptions());

        if ($handler->process($form, $request)) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('translator')->removeLocalesCacheFiles(array('nl', 'en'));

            return $this->redirect($this->generateUrl('admin.text.overview'));
        }

        return $this->render(
            'admin/text/edit.html.twig',
            array(
                'text' => $text,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @return Grid
     */
    private function buildGrid()
    {
        $grid = $this->get('grid');

        $source = new Entity('LexikTranslationBundle:TransUnit');
        $grid->setSource($source);

        $rowAction = new RowAction('edit', 'admin.text.edit');
        $grid->addRowAction($rowAction);

        $grid->setId('adminTextGrid');
        $grid->setRouteUrl($this->generateUrl('admin.text.grid'));

        $grid->setDefaultOrder('key', 'asc');
        $grid->setLimits(array(10, 25, 50));
        $grid->setDefaultLimit(25);

        $grid->setActionsColumnTitle('');

        $grid->hideColumns(array('id', 'createdAt', 'updatedAt'));

        $grid->getColumn('key')->setOperatorsVisible(false);
        $grid->getColumn('domain')->setFilterable(false);

        return $grid;
    }
}
