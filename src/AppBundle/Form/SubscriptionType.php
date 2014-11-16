<?php

namespace AppBundle\Form;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\Parser;
use AppBundle\Model\Contact;
use AppBundle\Model\Metadata;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SubscriptionType
 */
class SubscriptionType extends AbstractType
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
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact', new ContactType(), array('by_reference' => false))
            // Tab Metadata
            ->add('metadataUrl')
            ->add('acsLocation', null, array('read_only' => true)) // @todo: these should be disabled, but then validation is harder..
            ->add('entityId', null, array('read_only' => true))
            ->add('certificate', null, array('read_only' => true))
            ->add('logoUrl')
            ->add('nameEn')
            ->add('descriptionEn')
            ->add('nameNl')
            ->add('descriptionNl')
            ->add('applicationUrl')
            // Tab Contact
            ->add('administrativeContact', new ContactType(), array('by_reference' => false))
            ->add('technicalContact', new ContactType(), array('by_reference' => false))
            ->add('supportContact', new ContactType(), array('by_reference' => false))
            // Tab Attributes
            ->add('givenNameAttribute', new AttributeType(), array('by_reference' => false))
            ->add('surNameAttribute', new AttributeType(), array('by_reference' => false))
            ->add('commonNameAttribute', new AttributeType(), array('by_reference' => false))
            ->add('displayNameAttribute', new AttributeType(), array('by_reference' => false))
            ->add('emailAddressAttribute', new AttributeType(), array('by_reference' => false))
            ->add('organizationAttribute', new AttributeType(), array('by_reference' => false))
            ->add('organizationTypeAttribute', new AttributeType(), array('by_reference' => false))
            ->add('affiliationAttribute', new AttributeType(), array('by_reference' => false))
            ->add('entitlementAttribute', new AttributeType(), array('by_reference' => false))
            ->add('principleNameAttribute', new AttributeType(), array('by_reference' => false))
            ->add('isMemberOfAttribute', new AttributeType(), array('by_reference' => false))
            ->add('uidAttribute', new AttributeType(), array('by_reference' => false))
            ->add('preferredLanguageAttribute', new AttributeType(), array('by_reference' => false))
            // Tab Comments
            ->add('comments');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $subscription = $event->getData();

        // If metadataUrl is not submitted return early
        if (!array_key_exists('metadataUrl', $subscription)) {
            return;
        }

        /** @var Subscription $orgSubscription */
        $orgSubscription = $event->getForm()->getData();

        $metadataUrl = $subscription['metadataUrl'];
        $orgMetadataUrl = $orgSubscription->getMetadataUrl();

        $metadata = new Metadata();

        try {
            if ($metadataUrl !== $orgMetadataUrl) {
                $metadata = $this->parser->parse($metadataUrl);
            } else {
                $metadata = $this->getOriginalMetadata($orgSubscription, $subscription);
            }
        } catch (\InvalidArgumentException $e) {
            // Exceptions are deliberately ignored because they are caught by the validator
        }

        $event->setData($this->mapMetadataToFormData($subscription, $metadata));
    }

    private function mapMetadataToFormData(array $formData, Metadata $metadata)
    {
        $formData['acsLocation'] = $metadata->acsLocation;
        $formData['entityId'] = $metadata->entityId;
        $formData['certificate'] = $metadata->certificate;

        $formData['logoUrl'] = $metadata->logoUrl;
        $formData['nameEn'] = $metadata->nameEn;
        $formData['nameNl'] = $metadata->nameNl;
        $formData['descriptionEn'] = $metadata->descriptionEn;
        $formData['descriptionNl'] = $metadata->descriptionNl;
        $formData['applicationUrl'] = $metadata->applicationUrlEn;

        if ($metadata->administrativeContact instanceof Contact) {
            $formData['administrativeContact'] = array();
            $formData['administrativeContact']['firstName'] = $metadata->administrativeContact->getFirstName();
            $formData['administrativeContact']['lastName'] = $metadata->administrativeContact->getLastName();
            $formData['administrativeContact']['email'] = $metadata->administrativeContact->getEmail();
            $formData['administrativeContact']['phone'] = $metadata->administrativeContact->getPhone();
        }

        if ($metadata->technicalContact instanceof Contact) {
            $formData['technicalContact'] = array();
            $formData['technicalContact']['firstName'] = $metadata->technicalContact->getFirstName();
            $formData['technicalContact']['lastName'] = $metadata->technicalContact->getLastName();
            $formData['technicalContact']['email'] = $metadata->technicalContact->getEmail();
            $formData['technicalContact']['phone'] = $metadata->technicalContact->getPhone();
        }

        if ($metadata->supportContact instanceof Contact) {
            $formData['supportContact'] = array();
            $formData['supportContact']['firstName'] = $metadata->supportContact->getFirstName();
            $formData['supportContact']['lastName'] = $metadata->supportContact->getLastName();
            $formData['supportContact']['email'] = $metadata->supportContact->getEmail();
            $formData['supportContact']['phone'] = $metadata->supportContact->getPhone();
        }

        return $formData;
    }

    private function getOriginalMetadata(Subscription $subscription, array $formData)
    {
        $metadata = new Metadata();
        $metadata->acsLocation = $subscription->getAcsLocation();
        $metadata->entityId = $subscription->getEntityId();
        $metadata->certificate = $subscription->getCertificate();

        $metadata->logoUrl = array_key_exists('logoUrl', $formData) ? $formData['logoUrl'] : $subscription->getLogoUrl();
        $metadata->nameEn = array_key_exists('nameEn', $formData) ? $formData['nameEn'] : $subscription->getNameEn();
        $metadata->nameNl = array_key_exists('nameNl', $formData) ? $formData['nameNl'] : $subscription->getNameNl();
        $metadata->descriptionEn = array_key_exists('descriptionEn', $formData) ? $formData['descriptionEn'] : $subscription->getDescriptionEn();
        $metadata->descriptionNl = array_key_exists('descriptionNl', $formData) ? $formData['descriptionNl'] : $subscription->getDescriptionNl();
        $metadata->applicationUrlEn = array_key_exists('applicationUrl', $formData) ? $formData['applicationUrl'] : $subscription->getApplicationUrl();

        $metadata->administrativeContact = $this->getContactData($subscription, $formData, 'administrative');
        $metadata->technicalContact = $this->getContactData($subscription, $formData, 'technical');
        $metadata->supportContact = $this->getContactData($subscription, $formData, 'support');

        return $metadata;
    }

    private function getContactData(Subscription $subscription, array $formData, $type)
    {
        /** @var Contact $orgContact */
        $orgContact = $subscription->{'get' .ucfirst($type) . 'Contact'}();

        $contact = clone $orgContact;

        if (array_key_exists($type . 'Contact', $formData)) {
            $formData = $formData[$type . 'Contact'];

            if (array_key_exists('firstName', $formData)) {
                $contact->setFirstName($formData['firstName']);
            }

            if (array_key_exists('lastName', $formData)) {
                $contact->setLastName($formData['lastName']);
            }

            if (array_key_exists('email', $formData)) {
                $contact->setEmail($formData['email']);
            }

            if (array_key_exists('phone', $formData)) {
                $contact->setPhone($formData['phone']);
            }
        }

        return $contact;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'AppBundle\Entity\Subscription'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'subscription';
    }
}
