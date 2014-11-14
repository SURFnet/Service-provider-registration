<?php

namespace AppBundle\Model;

use AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class Metadata
{
    public $acsLocation;

    public $entityId;

    public $certificate;
}
