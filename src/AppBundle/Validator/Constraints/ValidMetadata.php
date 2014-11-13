<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidMetadata extends Constraint
{
    public $message = 'The metadata is invalid.';
}
