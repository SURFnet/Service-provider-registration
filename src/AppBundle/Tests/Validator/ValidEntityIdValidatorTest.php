<?php

use AppBundle\Validator\Constraints\ValidEntityIdValidator;

/**
 * Class TestValidEntityIdValidator
 *
 * So... the janus ConnectionDescriptorRepository class is final.
 * Which is the required contructor argument of the ValidEntityIdValidator.
 * Requiring this subclass...
 * @todo don't make library classes final.
 */
class TestValidEntityIdValidator extends ValidEntityIdValidator
{
    public function __construct($mock)
    {
        $object = new ReflectionObject($this);
        $property = $object->getParentClass()->getProperty('janus');
        $property->setAccessible(true);
        $property->setValue($this, $mock);
    }
}

class TestConnectionDescriptorRepository
{
    public function findByName($name)
    {
        return NULL;
    }
}

/**
 * Class ValidEntityIdValidatorTest
 */
class ValidEntityIdValidatorTest extends \Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest
{
    protected function getApiVersion()
    {
        return \Symfony\Component\Validator\Validation::API_VERSION_2_4;
    }

    protected function createValidator()
    {
        return new TestValidEntityIdValidator(new TestConnectionDescriptorRepository());
    }

    public function testSuccess()
    {
        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setMetadataUrl('https://www.domain.org');

        $this->setRoot($subscription);

        $this->validator->validate('https://sub.domain.org', new \AppBundle\Validator\Constraints\ValidEntityId());

        $this->assertNoViolation();
    }

    public function testEmptyEntityId()
    {
        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setMetadataUrl('https://www.domain.org');

        $this->setRoot($subscription);

        $this->validator->validate(null, new \AppBundle\Validator\Constraints\ValidEntityId());

        $this->assertNoViolation();
    }

    public function testEmptyMetadataUrl()
    {
        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setMetadataUrl('');

        $this->setRoot($subscription);

        $this->validator->validate('domain.org', new \AppBundle\Validator\Constraints\ValidEntityId());

        $this->assertNoViolation();
    }

    public function testInvalidDomain()
    {
        $domainA = 'invaliddomain.org';
        $domainB = 'domain.org';

        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setMetadataUrl('https://www.' . $domainA);

        $this->setRoot($subscription);

        $constraint = new \AppBundle\Validator\Constraints\ValidEntityId();
        $this->validator->validate('https://sub.' . $domainB, $constraint);

        $this->assertViolation(
            $constraint->message,
            array(
                '%mdomain%' => $domainA,
                '%edomain%' => $domainB
            )
        );
    }

    public function testInvalidEntityIdUrl()
    {
        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setMetadataUrl('www.domain.org');

        $this->setRoot($subscription);

        $constraint = new \AppBundle\Validator\Constraints\ValidEntityId();
        $this->validator->validate('q$:\₪.3%$', $constraint);

        $this->assertViolation('Invalid entityId.');
    }

    public function testInvalidMetadataUrl()
    {
        $subscription = new \AppBundle\Entity\Subscription();
        $subscription->setMetadataUrl('q$:\₪.3%$');

        $this->setRoot($subscription);

        $constraint = new \AppBundle\Validator\Constraints\ValidEntityId();
        $this->validator->validate('domain.org', $constraint);

        $this->assertViolation('Invalid metadataUrl.', array(), 'property.path.metadataUrl');
    }
}
