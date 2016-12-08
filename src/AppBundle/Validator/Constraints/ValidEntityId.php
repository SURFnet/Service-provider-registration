<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidEntityId extends Constraint
{
    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'entity_id';
    }
}
