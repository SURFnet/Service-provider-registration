<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidTwigTemplate extends Constraint
{
    public $message = 'Value is not a valid Twig template.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'twig_template';
    }
}
