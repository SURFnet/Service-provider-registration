<?php

namespace AppBundle\Model;

use AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Metadata
 *
 * @todo: spread props over more classes, also see Subscription Entity
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Metadata
{
    public $acsLocation;

    public $entityId;

    public $certificate;

    public $logoUrl;

    public $nameEn;

    public $nameNl;

    public $descriptionEn;

    public $descriptionNl;

    public $applicationUrlEn;

    public $applicationUrlNl;

    /**
     * @var Contact
     */
    public $administrativeContact;

    /**
     * @var Contact
     */
    public $supportContact;

    /**
     * @var Contact
     */
    public $technicalContact;

    /**
     * @var Attribute
     */
    public $givenNameAttribute;

    /**
     * @var Attribute
     */
    public $surNameAttribute;

    /**
     * @var Attribute
     */
    public $commonNameAttribute;

    /**
     * @var Attribute
     */
    public $displayNameAttribute;

    /**
     * @var Attribute
     */
    public $emailAddressAttribute;

    /**
     * @var Attribute
     */
    public $organizationAttribute;

    /**
     * @var Attribute
     */
    public $organizationTypeAttribute;

    /**
     * @var Attribute
     */
    public $affiliationAttribute;

    /**
     * @var Attribute
     */
    public $entitlementAttribute;

    /**
     * @var Attribute
     */
    public $principleNameAttribute;

    /**
     * @var Attribute
     */
    public $uidAttribute;

    /**
     * @var Attribute
     */
    public $preferredLanguageAttribute;

    /**
     * @var Attribute
     */
    public $personalCodeAttribute;

    /**
     * @var Attribute
     */
    public $scopedAffiliationAttribute;

    /**
     * @var Attribute
     */
    public $eduPersonTargetedIDAttribute;
}
