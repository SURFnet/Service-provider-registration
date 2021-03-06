<?php

namespace AppBundle\Entity;

use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use AppBundle\Validator\Constraints as AppAssert;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RuntimeException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Class Subscription
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\DoctrineSubscriptionRepository")
 * @GRID\Source(
 *      columns="id, ticketNo, contact, created, updated, status, environment, archived"
 * )
 *
 * @todo: spread props over more classes
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Subscription
{
    const STATE_DRAFT = 0;
    const STATE_PUBLISHED = 1;
    const STATE_FINISHED = 2;
    const ENVIRONMENT_CONNECT = 'connect';
    const ENVIRONMENT_PRODUCTION = 'production';
    const LANG_EN = 'en';
    const LANG_NL = 'nl';

    /**
     * @var string
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @GRID\Column(visible=false)
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank(groups={"creation"})
     * @Assert\Choice(choices = {"en", "nl"}, groups={"creation"})
     */
    private $locale = self::LANG_EN;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @GRID\Column(
     *      filter="select",
     *      selectFrom="values",
     *      values={false="No",true="Yes"}
     * )
     */
    private $archived = false;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @GRID\Column(
     *      operatorsVisible=false,
     *      filter="select",
     *      selectFrom="values",
     *      values={"connect","production"}
     * )
     * @Assert\NotBlank(groups={"creation"})
     * @Assert\Choice(choices = {"production", "connect"}, groups={"creation"})
     */
    private $environment = self::ENVIRONMENT_CONNECT;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @GRID\Column(
     *      operatorsVisible=false,
     *      filter="select",
     *      selectFrom="values",
     *      values={0="Draft",1="Published",2="Finished"}
     * )
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @GRID\Column(filterable=false)
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @GRID\Column(filterable=false)
     */
    private $updated;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @GRID\Column(operatorsVisible=false)
     * @Assert\NotBlank(groups={"creation"})
     */
    private $ticketNo;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $janusId;

    /**
     * @var Contact
     * @ORM\Column(type="object", nullable=true)
     * @GRID\Column(type="text", operatorsVisible=false)
     * @Assert\Type(type="AppBundle\Model\Contact", groups={"Default", "creation"})
     * @Assert\NotBlank(groups={"Default", "creation"})
     * @Assert\Valid()
     */
    private $contact;

    /**
     * Metadata URL that import last happened from.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Url(
     *      protocols={"https"},
     *      message = "url.notSecure",
     *      groups={"finished"}
     * )
     * @Assert\Url(message = "url.invalid")
     * @AppAssert\ValidMetadata()
     */
    private $importUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $metadataUrl;

    /**
     * SAML XML Metadata for entity.
     *
     * Imported from metadataurl.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $metadataXml;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"https","http"})
     * @Assert\Url(
     *      protocols={"https"},
     *      message = "url.notSecure",
     *      groups={"finished"}
     * )
     */
    private $acsLocation;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Url()
     * @AppAssert\ValidEntityId()
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
     * @Assert\NotBlank()
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
     * @Assert\Length(max = 300)
     */
    private $descriptionNl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max = 300)
     */
    private $descriptionEn;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    private $applicationUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    private $eulaUrl;

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
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $commonNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $displayNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $emailAddressAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $organizationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $organizationTypeAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $affiliationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $entitlementAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $principleNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $uidAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $preferredLanguageAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $personalCodeAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $scopedAffiliationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $eduPersonTargetedIDAttribute;

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
     * @return bool
     */
    public function isDraft()
    {
        return $this->status === self::STATE_DRAFT;
    }

    /**
     * @return $this
     */
    public function publish()
    {
        if ($this->status === self::STATE_PUBLISHED) {
            return $this;
        }

        if (!$this->isForConnect()) {
            throw new RuntimeException(
                "Invalid transition for production subscription"
            );
        }
        if (!$this->isDraft()) {
            throw new RuntimeException(
                "Invalid transition from {$this->status} to published"
            );
        }

        $this->status = self::STATE_PUBLISHED;

        return $this;
    }

    /**
     * @return $this
     */
    public function revertToPublished()
    {
        if ($this->status === self::STATE_PUBLISHED) {
            return $this;
        }

        if (!$this->isForConnect()) {
            throw new RuntimeException(
                "Invalid transition for production subscription"
            );
        }
        if (!$this->isFinished()) {
            throw new RuntimeException(
                "Invalid transition from {$this->status} back to published"
            );
        }

        $this->status = self::STATE_PUBLISHED;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === self::STATE_PUBLISHED;
    }

    /**
     *
     */
    public function finish()
    {
        if ($this->status === self::STATE_FINISHED) {
            return $this;
        }

        if ($this->isForConnect() && !$this->isPublished()) {
            throw new RuntimeException(
                "May not skip published for connect subscriptions"
            );
        }

        $this->status = self::STATE_FINISHED;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->status === self::STATE_FINISHED;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     *
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
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
     * @return string
     */
    public function getJanusId()
    {
        return $this->janusId;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setJanusId($id)
    {
        $this->janusId = $id;

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
     * @return string
     */
    public function getEulaUrl()
    {
        return $this->eulaUrl;
    }

    /**
     * @param string $eulaUrl
     *
     * @return $this
     */
    public function setEulaUrl($eulaUrl)
    {
        $this->eulaUrl = $eulaUrl;

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
     * @return Attribute
     */
    public function getCommonNameAttribute()
    {
        return $this->commonNameAttribute;
    }

    /**
     * @param Attribute $commonNameAttribute
     *
     * @return $this
     */
    public function setCommonNameAttribute($commonNameAttribute)
    {
        $this->commonNameAttribute = $commonNameAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getDisplayNameAttribute()
    {
        return $this->displayNameAttribute;
    }

    /**
     * @param Attribute $displayNameAttribute
     *
     * @return $this
     */
    public function setDisplayNameAttribute($displayNameAttribute)
    {
        $this->displayNameAttribute = $displayNameAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getEmailAddressAttribute()
    {
        return $this->emailAddressAttribute;
    }

    /**
     * @param Attribute $emailAddressAttribute
     *
     * @return $this
     */
    public function setEmailAddressAttribute($emailAddressAttribute)
    {
        $this->emailAddressAttribute = $emailAddressAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getOrganizationAttribute()
    {
        return $this->organizationAttribute;
    }

    /**
     * @param Attribute $organizationAttribute
     *
     * @return $this
     */
    public function setOrganizationAttribute($organizationAttribute)
    {
        $this->organizationAttribute = $organizationAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getOrganizationTypeAttribute()
    {
        return $this->organizationTypeAttribute;
    }

    /**
     * @param Attribute $organizationTypeAttribute
     *
     * @return $this
     */
    public function setOrganizationTypeAttribute($organizationTypeAttribute)
    {
        $this->organizationTypeAttribute = $organizationTypeAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getAffiliationAttribute()
    {
        return $this->affiliationAttribute;
    }

    /**
     * @param Attribute $affiliationAttribute
     *
     * @return $this
     */
    public function setAffiliationAttribute($affiliationAttribute)
    {
        $this->affiliationAttribute = $affiliationAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getEntitlementAttribute()
    {
        return $this->entitlementAttribute;
    }

    /**
     * @param Attribute $entitlementAttribute
     *
     * @return $this
     */
    public function setEntitlementAttribute($entitlementAttribute)
    {
        $this->entitlementAttribute = $entitlementAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getPrincipleNameAttribute()
    {
        return $this->principleNameAttribute;
    }

    /**
     * @param Attribute $principleNameAttribute
     *
     * @return $this
     */
    public function setPrincipleNameAttribute($principleNameAttribute)
    {
        $this->principleNameAttribute = $principleNameAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getUidAttribute()
    {
        return $this->uidAttribute;
    }

    /**
     * @param Attribute $uidAttribute
     *
     * @return $this
     */
    public function setUidAttribute($uidAttribute)
    {
        $this->uidAttribute = $uidAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getPreferredLanguageAttribute()
    {
        return $this->preferredLanguageAttribute;
    }

    /**
     * @param Attribute $preferredLanguageAttribute
     *
     * @return $this
     */
    public function setPreferredLanguageAttribute($preferredLanguageAttribute)
    {
        $this->preferredLanguageAttribute = $preferredLanguageAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getPersonalCodeAttribute()
    {
        return $this->personalCodeAttribute;
    }

    /**
     * @param Attribute $personalCodeAttribute
     *
     * @return Subscription
     */
    public function setPersonalCodeAttribute($personalCodeAttribute)
    {
        $this->personalCodeAttribute = $personalCodeAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getScopedAffiliationAttribute()
    {
        return $this->scopedAffiliationAttribute;
    }

    /**
     * @param Attribute $scopedAffiliationAttribute
     * @return $this
     */
    public function setScopedAffiliationAttribute($scopedAffiliationAttribute)
    {
        $this->scopedAffiliationAttribute = $scopedAffiliationAttribute;

        return $this;
    }

    /**
     * @return Attribute
     */
    public function getEduPersonTargetedIDAttribute()
    {
        return $this->eduPersonTargetedIDAttribute;
    }

    /**
     * @param Attribute $eduPersonTargetedIDAttribute
     * @return $this
     */
    public function setEduPersonTargetedIDAttribute($eduPersonTargetedIDAttribute)
    {
        $this->eduPersonTargetedIDAttribute = $eduPersonTargetedIDAttribute;

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

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (
            empty($this->technicalContact) ||
            empty($this->administrativeContact) ||
            $this->technicalContact->getFirstName() == false ||
            $this->technicalContact->getLastName() == false ||
            $this->technicalContact->getEmail() == false
        ) {
            return;
        }

        if (
            $this->technicalContact->getFirstName() !== $this->administrativeContact->getFirstName() ||
            $this->technicalContact->getLastName() !== $this->administrativeContact->getLastName() ||
            $this->technicalContact->getEmail() !== $this->administrativeContact->getEmail()
        ) {
            return;
        }

        $context->addViolationAt(
            'technicalContact.email', // @todo: at email path??
            'The technical contact should be different from the administrative contact.'
        );
    }

    /**
     * @return Subscription
     */
    public function archive()
    {
        $this->archived = true;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return bool
     */
    public function isForProduction()
    {
        return $this->environment === static::ENVIRONMENT_PRODUCTION;
    }

    /**
     * @return bool
     */
    public function isForConnect()
    {
        return $this->environment === static::ENVIRONMENT_CONNECT;
    }

    /**
     * @return string
     */
    public function getImportUrl()
    {
        return $this->importUrl;
    }

    /**
     * @param string $importUrl
     */
    public function setImportUrl($importUrl)
    {
        $this->importUrl = $importUrl;
    }

    /**
     * @return string
     */
    public function getMetadataXml()
    {
        return $this->metadataXml;
    }

    /**
     * @param string $metadataXml
     */
    public function setMetadataXml($metadataXml)
    {
        $this->metadataXml = $metadataXml;
    }
}
