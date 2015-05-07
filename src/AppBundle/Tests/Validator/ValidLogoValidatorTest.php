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

    public function testBigImage()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate(__DIR__ . '/Fixtures/image.png', $constraint);

        $this->assertViolation('Logo is too big, it should be max. 500 x 300 px.');
    }

    public function testWidthImage()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate(__DIR__ . '/Fixtures/width.png', $constraint);

        $this->assertViolation('Logo is too big, it should be max. 500 x 300 px.');
    }

    public function testHeightImage()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate(__DIR__ . '/Fixtures/height.png', $constraint);

        $this->assertViolation('Logo is too big, it should be max. 500 x 300 px.');
    }

    public function testInvalidSize()
    {
        $constraint = new \AppBundle\Validator\Constraints\ValidLogo();
        $this->validator->validate('file://' . __DIR__ . '/Fixtures/large.png', $constraint);

        $this->assertViolation('Logo is too large, it should be max. 1MiB (1.048.576 bytes)');
    }
}
