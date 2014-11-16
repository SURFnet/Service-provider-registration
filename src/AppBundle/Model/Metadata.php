<?php

namespace AppBundle\Model;

use AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

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
}
