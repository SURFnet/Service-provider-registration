<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Metadata\Exception\ParserException;
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
        } catch (ParserException $e) {
            $this->context->addViolation($constraint->parseMessage, $this->processErrors($e->getParserErrors()));
        } catch (\Exception $e) {
            $this->context->addViolation($e->getMessage());

            return;
        }
    }

    /**
     * @param \LibXMLError[] $errors
     *
     * @return array
     */
    private function processErrors(array $errors)
    {
        $errorString = PHP_EOL;

        foreach ($errors as $error) {
            $errorString .= 'At line ' . $error->line . ', column ' . $error->column . ': ';
            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $errorString .= "Warning $error->code, ";
                    break;
                case LIBXML_ERR_ERROR:
                    $errorString .= "Error $error->code, ";
                    break;
                case LIBXML_ERR_FATAL:
                    $errorString .= "Fatal Error $error->code, ";
                    break;
            }

            $errorString .= trim($error->message) . PHP_EOL;
        }

        return array(
            '%errors%' => $errorString
        );
    }
}
