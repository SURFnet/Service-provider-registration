<?php
namespace AppBundle\Entity;

use AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Contact
{
    const STATE_DRAFT = 0;
    const STATE_FINISHED = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @Assert\EqualTo(value = 20)
     */
    private $city;

    /**
     * @ORM\Column(type="string")
     * @Assert\EqualTo(value = 20)
     */
    private $remote;

    /**
     * @ORM\Column(type="text")
     */
    private $comment;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @AppAssert\ValidSSLCertificate()
     */
    private $cert;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::STATE_DRAFT;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Contact
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     *
     * @return Contact
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     *
     * @return Contact
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemote()
    {
        return $this->remote;
    }

    /**
     * @param mixed $remote
     *
     * @return Contact
     */
    public function setRemote($remote)
    {
        $this->remote = $remote;

        return $this;
    }

    /**
     *
     */
    public function finish()
    {
        $this->status = self::STATE_FINISHED;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->status === self::STATE_FINISHED;
    }

    public function getLocale()
    {
        return 'nl';
    }

    /**
     * @return mixed
     */
    public function getCert()
    {
        return $this->cert;
    }

    /**
     * @param mixed $cert
     *
     * @return Contact
     */
    public function setCert($cert)
    {
        $this->cert = $cert;

        return $this;
    }
}
