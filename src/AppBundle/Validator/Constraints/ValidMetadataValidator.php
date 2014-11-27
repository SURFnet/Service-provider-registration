<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Metadata\Parser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidMetadataValidator
 */
class ValidMetadataValidator extends ConstraintValidator
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        try {
            $this->parser->parse($value);
        } catch (\Exception $e) {
            $this->context->addViolation($e->getMessage());

            return;
        }
    }
}
