<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Subscription;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidEntityIdValidator
 */
class ValidEntityIdValidator extends ConstraintValidator
{
    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var Subscription $subscription */
        $subscription = $this->context->getRoot()->getData();

        $metadataUrl = $subscription->getMetadataUrl();

        if (empty($metadataUrl) || empty($value)) {
            return;
        }

        $metadataHost = parse_url($metadataUrl, PHP_URL_HOST);
        $entityIdHost = parse_url($value, PHP_URL_HOST);

        if ($metadataHost === false) {
            $this->context->addViolationAt('metadataUrl', 'Invalid metadataUrl.');

            return;
        }

        if ($entityIdHost === false) {
            $this->context->addViolation('Invalid entityId.');

            return;
        }

        if ($metadataHost !== $entityIdHost) {
            $this->context->addViolation($constraint->message);

            return;
        }
   }
}
