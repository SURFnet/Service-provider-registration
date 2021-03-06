<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Subscription;
use OpenConext\JanusClient\Entity\ConnectionDescriptorRepository;
use Pdp\Parser;
use Pdp\PublicSuffixListManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidEntityIdValidator
 */
class ValidEntityIdValidator extends ConstraintValidator
{
    /**
     * @var ConnectionDescriptorRepository
     */
    private $janus;

    /**
     * @param ConnectionDescriptorRepository $janus
     */
    public function __construct(ConnectionDescriptorRepository $janus)
    {
        $this->janus = $janus;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $root = $this->context->getRoot();

        if ($root instanceof Subscription) {
            $subscription = $root;
        } else {
            $subscription = $root->getData();
        }

        if (!$subscription->isDraft()) {
            return;
        }

        $metadataUrl = $subscription->getMetadataUrl();

        if (empty($metadataUrl) || empty($value)) {
            return;
        }

        $pslManager = new PublicSuffixListManager();
        $parser = new Parser($pslManager->getList());

        try {
            $parser->parseUrl($metadataUrl);
        } catch (\Exception $e) {
            $this->context->addViolationAt('metadataUrl', 'Invalid metadataUrl.');

            return;
        }

        try {
            $parser->parseUrl($value);
        } catch (\Exception $e) {
            $this->context->addViolation('Invalid entityId.');

            return;
        }

        if ($subscription->isForProduction()) {
            return;
        }

        try {
            $entity = $this->janus->findByName($value);
        } catch (\Exception $e) {
            $this->context->addViolation('Failed checking registry.');

            return;
        }

        if (!$entity) {
            return;
        }

        $this->context->addViolation('Entity has already been registered.');
    }
}
