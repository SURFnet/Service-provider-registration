<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidSSLCertificate extends Constraint
{
    public $message = 'The certificate is not valid.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'certificate';
    }
}
