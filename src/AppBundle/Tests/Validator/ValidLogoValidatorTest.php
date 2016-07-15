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

    public function testSuccessPNG()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate('file://' .__DIR__ . '/Fixtures/small.png', $constraint);

        $this->assertNoViolation();
    }

    public function testSuccessGIF()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate('file://' . __DIR__ . '/Fixtures/small.gif', $constraint);

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
        $this->validator->validate(__DIR__ . '/Fixtures/image.jpg', $constraint);

        $this->assertViolation('Logo should be a PNG or GIF.');
    }
}
