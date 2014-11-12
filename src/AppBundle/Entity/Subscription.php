<?php

namespace AppBundle\Entity;

use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Subscription
 *
 * @ORM\Entity
 */
class Subscription
{
    const STATE_DRAFT = 0;
    const STATE_FINISHED = 1;

    /**
     * @var string
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $locale;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $ticketNo;

    /**
     * @var Contact
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Contact")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $contact;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"https"})
     */
    private $metadataUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"https"})
     */
    private $acsLocation;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $entityId;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * @AppAssert\ValidSSLCertificate()
     */
    private $certificate;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     * @AppAssert\ValidLogo()
     */
    private $logoUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameNl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameEn;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionNl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionEn;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    private $applicationUrl;

    /**
     * @var Contact
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Contact")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $administrativeContact;

    /**
     * @var Contact
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Contact")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $technicalContact;

    /**
     * @var Contact
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Contact")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $supportContact;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $givenNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $surNameAttribute;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::STATE_DRAFT;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Subscription
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

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

    /**
     * @return string
     */
    public function getTicketNo()
    {
        return $this->ticketNo;
    }

    /**
     * @param string $ticketNo
     *
     * @return Subscription
     */
    public function setTicketNo($ticketNo)
    {
        $this->ticketNo = $ticketNo;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     *
     * @return Subscription
     */
    public function setContact(Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetadataUrl()
    {
        return $this->metadataUrl;
    }

    /**
     * @param string $metadataUrl
     *
     * @return $this
     */
    public function setMetadataUrl($metadataUrl)
    {
        $this->metadataUrl = $metadataUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAcsLocation()
    {
        return $this->acsLocation;
    }

    /**
     * @param string $acsLocation
     *
     * @return $this
     */
    public function setAcsLocation($acsLocation)
    {
        $this->acsLocation = $acsLocation;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     *
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     *
     * @return $this
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @param string $logoUrl
     *
     * @return $this
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameNl()
    {
        return $this->nameNl;
    }

    /**
     * @param string $nameNl
     *
     * @return $this
     */
    public function setNameNl($nameNl)
    {
        $this->nameNl = $nameNl;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * @param string $nameEn
     *
     * @return $this
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescriptionNl()
    {
        return $this->descriptionNl;
    }

    /**
     * @param string $descriptionNl
     *
     * @return $this
     */
    public function setDescriptionNl($descriptionNl)
    {
        $this->descriptionNl = $descriptionNl;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    /**
     * @param string $descriptionEn
     *
     * @return $this
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationUrl()
    {
        return $this->applicationUrl;
    }

    /**
     * @param string $applicationUrl
     *
     * @return $this
     */
    public function setApplicationUrl($applicationUrl)
    {
        $this->applicationUrl = $applicationUrl;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getAdministrativeContact()
    {
        return $this->administrativeContact;
    }

    /**
     * @param Contact $administrativeContact
     *
     * @return $this
     */
    public function setAdministrativeContact(Contact $administrativeContact = null)
    {
        $this->administrativeContact = $administrativeContact;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getTechnicalContact()
    {
        return $this->technicalContact;
    }

    /**
     * @param Contact $technicalContact
     *
     * @return $this
     */
    public function setTechnicalContact(Contact $technicalContact = null)
    {
        $this->technicalContact = $technicalContact;

        return $this;
    }

    /**
     * @Assert\True(message = "The technical contact should be different from the administrative contact.")
     */
    public function isTechnicalContactDifferentFromAdministrativeContact()
    {
        if (
            $this->technicalContact->getFirstName() == false ||
            $this->technicalContact->getLastName() == false ||
            $this->technicalContact->getEmail() == false
        ) {
            return true;
        }

        return (
            $this->technicalContact->getFirstName() !== $this->administrativeContact->getFirstName() ||
            $this->technicalContact->getLastName() !== $this->administrativeContact->getLastName() ||
            $this->technicalContact->getEmail() !== $this->administrativeContact->getEmail()
        );
    }

    /**
     * @return Contact
     */
    public function getSupportContact()
    {
        return $this->supportContact;
    }

    /**
     * @param Contact $supportContact
     *
     * @return $this
     */
    public function setSupportContact(Contact $supportContact = null)
    {
        $this->supportContact = $supportContact;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getGivenNameAttribute()
    {
        return $this->givenNameAttribute;
    }

    /**
     * @param Attribute $givenNameAttribute
     *
     * @return $this
     */
    public function setGivenNameAttribute(Attribute $givenNameAttribute = null)
    {
        $this->givenNameAttribute = $givenNameAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getSurNameAttribute()
    {
        return $this->surNameAttribute;
    }

    /**
     * @param Attribute $surNameAttribute
     *
     * @return $this
     */
    public function setSurNameAttribute(Attribute $surNameAttribute = null)
    {
        $this->surNameAttribute = $surNameAttribute;

        return $this;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     *
     * @return Subscription
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }
}
