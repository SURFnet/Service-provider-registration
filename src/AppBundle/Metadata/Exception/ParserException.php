<?php

namespace AppBundle\Metadata\Exception;

/**
 * ParserException
 */
class ParserException extends \InvalidArgumentException
{
    /**
     * @var \LibXMLError[]
     */
    private $parserErrors;

    /**
     * @param \LibXMLError[] $errors
     */
    public function setParserErrors(array $errors)
    {
        $this->parserErrors = $errors;
    }

    /**
     * @return \LibXMLError[]
     */
    public function getParserErrors()
    {
        return $this->parserErrors;
    }
}
