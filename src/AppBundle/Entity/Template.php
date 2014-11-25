<?php

namespace AppBundle\Entity;

use AppBundle\Validator\Constraints as AppAssert;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Template
 *
 * @ORM\Entity
 * @GRID\Source(columns="id, name", filterable=false)
 */
class Template
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @GRID\Column(visible=false)
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @AppAssert\ValidTwigTemplate()
     */
    private $source;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $modified;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Template
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return Template
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $modified
     *
     * @return Template
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }
}
