<?php

/**
 * Class ValidSSLCertificateValidatorTest
 */
class ValidSSLCertificateValidatorTest extends \Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest
{
    /**
     * @var \AppBundle\Metadata\CertificateFetcher
     */
    private $fetcher;

    /**
     * @var \AppBundle\Metadata\CertificateParser
     */
    private $parser;

    protected function getApiVersion()
    {
        return \Symfony\Component\Validator\Validation::API_VERSION_2_4;
    }

    protected function createValidator()
    {
        $this->fetcher = $this->getMockBuilder('\AppBundle\Metadata\CertificateFetcher')->getMock();
        $this->fetcher->method('fetch')->willReturn(file_get_contents(__DIR__ . '/Fixtures/abn.cer'));

        $this->parser = new \AppBundle\Metadata\CertificateParser();

        return new \AppBundle\Validator\Constraints\ValidSSLCertificateValidator($this->fetcher, $this->parser);
    }

    public function testSuccess()
    {
        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setAcsLocation('q');

        $this->setRoot($subscription);

        $cert = file_get_contents(__DIR__ . '/Fixtures/certificate.cer');
        $this->validator->validate($cert, new \AppBundle\Validator\Constraints\ValidSSLCertificate());

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

    public function testInvalidAcsLocation()
    {
        $this->fetcher->method('fetch')->will($this->throwException(new \InvalidArgumentException));

        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setAcsLocation('q');

        $this->setRoot($subscription);

        $cert = file_get_contents(__DIR__ . '/Fixtures/certificate.cer');
        $this->validator->validate($cert, new \AppBundle\Validator\Constraints\ValidSSLCertificate());

        $this->assertViolation('Unable to retrieve SSL certificate of ACSLocation.');
    }

    public function testMatchingAcsLocation()
    {
        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setAcsLocation('q');

        $this->setRoot($subscription);

        $cert = $this->parser->parse(file_get_contents(__DIR__ . '/Fixtures/abn.cer'));
        $this->validator->validate($cert, new \AppBundle\Validator\Constraints\ValidSSLCertificate());

        $this->assertViolation('Certificate matches certificate of ACSLocation which is not allowed.');
    }
}
