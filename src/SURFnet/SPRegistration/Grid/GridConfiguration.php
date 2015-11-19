<?php

namespace SURFnet\SPRegistration\Grid;

use AppBundle\Entity\Subscription;
use AppBundle\Model\Contact;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Source\Entity;

/**
 * Class GridConfiguration
 *
 * @package SURFnet\SPRegistration
 */
class GridConfiguration
{
    /**
     * @param Grid   $grid
     * @param string $routeUrl
     *
     * @return Grid
     */
    public function configureGrid(Grid $grid, $routeUrl)
    {
        $this->configureGridSource($grid);

        $this->configureGridColumns($grid);

        $this->configureRowActions($grid);

        $grid->setId('adminGrid');
        $grid->setRouteUrl($routeUrl);

        $grid->setDefaultOrder('created', 'desc');
        $grid->setDefaultFilters(
            array(
                'archived' => false,
            )
        );
        $grid->setLimits(array(5 => 5, 10 => 10, 15 => 15, 25 => 25, 50 => 50, 99999 => 'all'));

        $grid->setActionsColumnTitle('');
        $grid->setPersistence(true);

        return $grid;
    }

    /**
     * @param Grid $grid
     */
    private function configureGridSource(Grid $grid)
    {
        $source = new Entity('AppBundle:Subscription');
        $source->manipulateRow(
            function (Row $row) {
                if ($row->getField('status') == Subscription::STATE_FINISHED) {
                    $row->setClass('success');
                }

                if ($row->getField('status') == Subscription::STATE_PUBLISHED) {
                    $row->setClass('info');
                }

                return $row;
            }
        );
        $grid->setSource($source);
    }

    /**
     * @param Grid $grid
     */
    private function configureGridColumns(Grid $grid)
    {
        $grid->getColumn('contact')->manipulateRenderCell(
            function (Contact $contact) {
                $name = trim($contact->getFirstName() . ' ' . $contact->getLastName());

                $email = $contact->getEmail();
                if (!empty($email)) {
                    return $name . ' (' . $email . ')';
                }

                return $name;
            }
        );

        $grid->getColumn('status')->manipulateRenderCell(
            function ($status) {
                switch ($status) {
                    case Subscription::STATE_FINISHED:
                        return 'Finished';

                    case Subscription::STATE_PUBLISHED:
                        return 'Published';

                    case Subscription::STATE_DRAFT:
                        return 'Draft';

                    default:
                        return 'Unknown';
                }
            }
        );
    }

    /**
     * @param Grid $grid
     */
    private function configureRowActions(Grid $grid)
    {
        $this->addViewLink($grid);

        $this->addEditLink($grid);

        $this->addFinishLink($grid);

        $this->addPublishLink($grid);

        $this->addArchiveLink($grid);

        $this->addLinkToJanus($grid);
    }

    /**
     * @param Grid $grid
     */
    private function addViewLink(Grid $grid)
    {
        $rowAction = new RowAction('view', 'admin.subscription.view');
        $grid->addRowAction($rowAction);
    }

    /**
     * @param Grid $grid
     */
    private function addEditLink(Grid $grid)
    {
        $rowAction = new RowAction('edit', 'form', false, '_blank');
        $rowAction->manipulateRender(
            function (RowAction $action, Row $row) {
                if ($row->getField('status') == Subscription::STATE_FINISHED) {
                    return null;
                }

                return $action;
            }
        );
        $grid->addRowAction($rowAction);
    }

    /**
     * @param Grid $grid
     */
    private function addFinishLink(Grid $grid)
    {
        $rowAction = new RowAction('finish', 'admin.subscription.finish', true);
        $rowAction->manipulateRender(
            function (RowAction $action, Row $row) {
                if ($row->getField('status') !== Subscription::STATE_PUBLISHED) {
                    return null;
                }

                return $action;
            }
        );
        $grid->addRowAction($rowAction);
    }

    /**
     * @param Grid $grid
     */
    private function addPublishLink(Grid $grid)
    {
        $rowAction = new RowAction(
            'revert to published',
            'admin.subscription.publish',
            true
        );
        $rowAction->manipulateRender(
            function (RowAction $action, Row $row) {
                if ($row->getField('status') !== Subscription::STATE_FINISHED) {
                    return null;
                }

                return $action;
            }
        );
        $grid->addRowAction($rowAction);
    }

    /**
     * @param Grid $grid
     */
    private function addArchiveLink(Grid $grid)
    {
        $rowAction = new RowAction('archive', 'admin.subscription.archive');
        $rowAction->manipulateRender(
            function (RowAction $action, Row $row) {
                if ($row->getField('archived') === true) {
                    return null;
                }
                if ($row->getField('status') !== Subscription::STATE_FINISHED) {
                    return null;
                }

                return $action;
            }
        );
        $grid->addRowAction($rowAction);
    }

    /**
     * @param Grid $grid
     */
    private function addLinkToJanus(Grid $grid)
    {
        $rowAction = new RowAction('janus', 'admin.subscription.janus');
        $rowAction->manipulateRender(
            function (RowAction $action, Row $row) {
                $subscription = $row->getEntity();

                if (!$subscription instanceof Subscription) {
                    return null;
                }

                if (!$subscription->getJanusId()) {
                    return null;
                }

                return $action->addRouteParameters(
                    array(
                        'eid' => $subscription->getJanusId(),
                    )
                );
            }
        );
        $grid->addRowAction($rowAction);
    }
}
