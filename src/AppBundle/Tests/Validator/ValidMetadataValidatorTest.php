<?php

/**
 * Class ValidMetadataValidatorTest
 */
class ValidMetadataValidatorTest extends \Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest
{
    /**
     * @var \Guzzle\Http\Message\Response
     */
    private $mockResponse;

    protected function getApiVersion()
    {
        return \Symfony\Component\Validator\Validation::API_VERSION_2_4;
    }

    protected function createValidator()
    {
        $this->mockResponse = new \Guzzle\Http\Message\Response(200);
        $this->mockResponse->setBody(fopen(__DIR__ . '/Fixtures/metadata.xml', 'r+'));

        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse($this->mockResponse);

        $guzzle = new \Guzzle\Http\Client();
        $guzzle->addSubscriber($plugin);

        $parser = new \AppBundle\Metadata\Parser(
            $guzzle,
            new \AppBundle\Metadata\CertificateParser(),
            new \Doctrine\Common\Cache\ArrayCache(),
            __DIR__ . '/../../../../app/Resources/schemas/'
        );

        return new \AppBundle\Validator\Constraints\ValidMetadataValidator($parser);
    }

    public function testSuccess()
    {
        $this->validator->validate('https://domain.org/metadata', new \AppBundle\Validator\Constraints\ValidMetadata());

        $this->assertNoViolation();
    }

    public function testEmptyValue()
    {
        $this->validator->validate(null, new \AppBundle\Validator\Constraints\ValidMetadata());

        $this->assertNoViolation();
    }

    public function testInvalidMetadata()
    {
        $this->mockResponse->setBody(fopen(__DIR__ . '/Fixtures/invalid_metadata.xml', 'r+'));

        $constraint = new \AppBundle\Validator\Constraints\ValidMetadata();
        $this->validator->validate('9j7hd6ijk5', $constraint);

        $this->assertNotCount(0, $this->context->getViolations());
    }
}
