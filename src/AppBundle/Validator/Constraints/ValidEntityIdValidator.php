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

        $metadataUrl = $subscription->getMetadataUrl();

        if (empty($metadataUrl) || empty($value)) {
            return;
        }

        $pslManager = new PublicSuffixListManager();
        $parser = new Parser($pslManager->getList());

        try {
            $metadataUrl = $parser->parseUrl($metadataUrl);
        } catch (\Exception $e) {
            $this->context->addViolationAt('metadataUrl', 'Invalid metadataUrl.');

            return;
        }

        try {
            $entityIdUrl = $parser->parseUrl($value);
        } catch (\Exception $e) {
            $this->context->addViolation('Invalid entityId.');

            return;
        }

        if ($metadataUrl->host->registerableDomain !== $entityIdUrl->host->registerableDomain) {
            $this->context->addViolation(
                $constraint->message,
                array(
                    '%mdomain%' => $metadataUrl->host->registerableDomain,
                    '%edomain%' => $entityIdUrl->host->registerableDomain
                )
            );

            return;
        }

        if ($this->janus->findByName($value)) {
            $this->context->addViolation('Entity has already been registered.');

            return;
        }
    }
}
