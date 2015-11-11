<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MenuBuilder
 */
class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Request $request
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createAdminMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Subscriptions', array('route' => 'admin.subscription.overview'));
        $menu->addChild('Templates', array('route' => 'admin.template.overview'));
        $menu->addChild('Texts', array('route' => 'admin.text.overview'));

        return $menu;
    }
}
