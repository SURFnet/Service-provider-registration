<?php

/**
 * Class ValidLogoValidatorTest
 */
class ValidLogoValidatorTest extends \Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest
{
    protected function getApiVersion()
    {
        return \Symfony\Component\Validator\Validation::API_VERSION_2_4;
    }

    protected function createValidator()
    {
        return new \AppBundle\Validator\Constraints\ValidLogoValidator();
    }

    public function testSuccess()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate(__DIR__ . '/Fixtures/image.png', $constraint);

        $this->assertNoViolation();
    }

    public function testEmptyValue()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function testInvalidImage()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate('ufjd', $constraint);

        $this->assertViolation($constraint->message);
    }

    public function testInvalidType()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate(__DIR__ . '/Fixtures/image.gif', $constraint);

        $this->assertViolation('Logo should be a PNG.');
    }

    public function testSmallImage()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate(__DIR__ . '/Fixtures/small.png', $constraint);

        $this->assertViolation('Logo is too small, it should be at least 500 x 300 px.');
    }
}
