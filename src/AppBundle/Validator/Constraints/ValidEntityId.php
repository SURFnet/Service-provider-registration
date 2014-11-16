<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidEntityId extends Constraint
{
    public $message = 'Domain of entityId (%edomain%) must match domain of metadataUrl (%mdomain%).';
}
