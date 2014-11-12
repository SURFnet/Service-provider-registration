<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidLogoValidator
 */
class ValidLogoValidator extends ConstraintValidator
{
    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        // @todo: find a nicer way to get this info
        $imgData = @getimagesize($value);

        if ($imgData !== false) {
            list($width, $height, $type) = $imgData;

            if ($type !== IMAGETYPE_PNG) {
                $this->context->addViolation('Logo should be a PNG.');

                return;
            }

            if ($width < 500 && $height < 300) {
                $this->context->addViolation('Logo is too small.');

                return;
            }
        }

        $this->context->addViolation($constraint->message);
   }
}
