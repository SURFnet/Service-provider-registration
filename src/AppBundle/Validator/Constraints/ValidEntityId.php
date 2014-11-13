<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidEntityId extends Constraint
{
    public $message = 'Host of entityId must match host of metadataUrl.';
}
