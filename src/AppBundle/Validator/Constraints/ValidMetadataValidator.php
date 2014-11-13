<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidMetadataValidator
 */
class ValidMetadataValidator extends ConstraintValidator
{
    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        $metadata = true; //file_get_contents($value);

        if ($metadata === false) {
            $this->context->addViolation($constraint->message);

            return;
        }

        // @todo: parse and validate metadata
   }
}
