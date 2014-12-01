<?php

/**
 * Class ValidSSLCertificateValidatorTest
 */
class ValidSSLCertificateValidatorTest extends \Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest
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
        $this->mockResponse->setBody('');

        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse($this->mockResponse);

        $guzzle = new \Guzzle\Http\Client();
        $guzzle->addSubscriber($plugin);

        return new \AppBundle\Validator\Constraints\ValidSSLCertificateValidator($guzzle);
    }

    public function testSuccess()
    {
        $cert = file_get_contents(__DIR__ . '/Fixtures/certificate.cer');
        $this->validator->validate($cert, new \AppBundle\Validator\Constraints\ValidEntityId());

        $this->assertNoViolation();
    }

    public function testEmptyValue()
    {
        $this->validator->validate(null, new \AppBundle\Validator\Constraints\ValidSSLCertificate());

        $this->assertNoViolation();
    }

    public function testInvalidKey()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidSSLCertificate();

        $cert = file_get_contents(__DIR__ . '/Fixtures/invalid.cer');
        $this->validator->validate($cert, $constraint);

        $this->assertViolation($constraint->message);
    }

    public function testInvalidKeyLength()
    {
        $cert = file_get_contents(__DIR__ . '/Fixtures/google.cer');
        $this->validator->validate($cert, new \AppBundle\Validator\Constraints\ValidSSLCertificate());

        $this->assertViolation('Invalid key length');
    }
}
