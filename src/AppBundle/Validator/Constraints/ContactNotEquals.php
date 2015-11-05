<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContactNotEquals extends Constraint
{
    public $message = 'Contact must not be equal to technical contact.';
}
