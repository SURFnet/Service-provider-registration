<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Template;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Twig_ExistsLoaderInterface;
use Twig_LoaderInterface;

/**
 * Class DatabaseLoader
 */
class DatabaseLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param Cache         $cache
     */
    public function __construct(EntityManager $entityManager, Cache $cache)
    {
        $this->em = $entityManager;
        $this->cache = $cache;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws \Twig_Error_Loader
     */
    public function getSource($name)
    {
        if (!$this->exists($name)) {
            throw new \Twig_Error_Loader(sprintf('Template "%s" does not exist.', $name));
        }

        return $this->getTemplate($name, true)->getSource();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return $this->getTemplate($name) !== null;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getCacheKey($name)
    {
        return $name;
    }

    /**
     * @param string $name
     * @param int    $time
     *
     * @return bool
     */
    public function isFresh($name, $time)
    {
        return $this->getTemplate($name, true)->getModified()->getTimestamp() <= $time;
    }

    /**
     * @param string $name
     * @param bool   $force
     *
     * @return Template
     */
    protected function getTemplate($name, $force = false)
    {
        $cacheId = md5('tpl-' . $name);

        if (!$force) {
            $tpl = $this->cache->fetch($cacheId);
            if ($tpl) {
                return $tpl;
            }
        }

        $tpl = $this->em->getRepository('AppBundle:Template')->findOneBy(array('name' => $name));

        $this->cache->save($cacheId, $tpl, 60 * 60 * 24);

        return $tpl;
    }
}
