<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidLogo extends Constraint
{
    public $message = 'Logo is not a valid image.';
}
