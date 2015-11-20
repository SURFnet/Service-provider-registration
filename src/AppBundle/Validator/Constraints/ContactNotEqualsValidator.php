<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Subscription;
use AppBundle\Model\Contact;
use RuntimeException;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidEntityIdValidator
 */
class ContactNotEqualsValidator extends ConstraintValidator
{
    /**
     * @param Contact    $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $subscription = $this->getSubscriptionFromContext();

        if (!$subscription->getAdministrativeContact()) {
            return;
        }
        if (!$subscription->getTechnicalContact()) {
            return;
        }

        $adminEmail = $subscription->getAdministrativeContact()->getEmail();
        $techEmail  = $subscription->getTechnicalContact()->getEmail();

        if ($value !== $adminEmail) {
            return;
        }

        if ($adminEmail !== $techEmail) {
            return;
        }

        $this->context->addViolation('Admin email is same as technical.');
    }

    /**
     * @return Subscription
     */
    private function getSubscriptionFromContext()
    {
        $subscription = $this->context->getRoot();

        if ($subscription instanceof Form) {
            $subscription = $subscription->getData();
        }

        if (!$subscription instanceof Subscription) {
            throw new RuntimeException('Unable to get subscription from form');
        }

        return $subscription;
    }
}
