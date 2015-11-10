<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidSSLLabsAnalyze extends Constraint
{
    public $message = 'Required SSL Labs grade not met.';

    public function validatedBy()
    {
        return 'ssllabs';
    }
}
