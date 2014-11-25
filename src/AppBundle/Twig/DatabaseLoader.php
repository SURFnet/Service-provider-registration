<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Template;
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
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
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

        return $this->getTemplate($name)->getSource();
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
        return $this->getTemplate($name)->getModified()->getTimestamp() <= $time;
    }

    /**
     * @param string $name
     *
     * @return Template
     */
    protected function getTemplate($name)
    {
        return $this->em->getRepository('AppBundle:Template')->findOneBy(array('name' => $name));
    }
}
