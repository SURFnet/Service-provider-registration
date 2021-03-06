<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidMetadata extends Constraint
{
    public $message = 'The metadata is invalid.';

    public $parseMessage = 'Invalid metadata: %errors%';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'metadata';
    }
}
