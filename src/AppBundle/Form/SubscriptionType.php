<?php

namespace AppBundle\Form;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\Parser;
use AppBundle\Model\Attribute;
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
            ->add('metadataUrl', 'url', array('default_protocol' => 'https'))
            ->add('acsLocation', null, array('read_only' => true)) // @todo: these should be disabled, but then validation is harder..
            ->add('entityId', null, array('read_only' => true))
            ->add('certificate', null, array('read_only' => true))
            ->add('logoUrl')
            ->add('nameEn')
            ->add('descriptionEn')
            ->add('nameNl')
            ->add('descriptionNl')
            ->add('applicationUrl');

        // Tab Contact
        foreach ($this->getContacts() as $contact) {
            $builder->add($contact, new ContactType(), array('by_reference' => false));
        }

        // Tab Attributes
        foreach ($this->getAttributes() as $attribute) {
            $builder->add($attribute, new AttributeType(), array('by_reference' => false));
        }

        // Tab Comments
        $builder->add('comments');

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
            if ($metadataUrl != $orgMetadataUrl) {
                $metadata = $this->parser->parse($metadataUrl);
            } else {
                $metadata = $this->getOriginalMetadata($orgSubscription, $subscription);
            }
        } catch (\InvalidArgumentException $e) {
            // Exceptions are deliberately ignored because they are caught by the validator
        }

        $event->setData($this->mapMetadataToFormData($subscription, $metadata));
    }

    /**
     * @param array    $formData
     * @param Metadata $metadata
     *
     * @return array
     */
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

        foreach ($this->getContacts() as $contact) {
            if ($metadata->$contact instanceof Contact) {
                $formData[$contact] = array();
                $formData[$contact]['firstName'] = $metadata->$contact->getFirstName();
                $formData[$contact]['lastName'] = $metadata->$contact->getLastName();
                $formData[$contact]['email'] = $metadata->$contact->getEmail();
                $formData[$contact]['phone'] = $metadata->$contact->getPhone();
            }
        }

        foreach ($this->getAttributes() as $attribute) {
            if ($metadata->$attribute instanceof Attribute) {
                $formData[$attribute] = array();
                $formData[$attribute]['requested'] = $metadata->$attribute->isRequested();
                $formData[$attribute]['motivation'] = $metadata->$attribute->getMotivation();
            }
        }

        return $formData;
    }

    /**
     * @param Subscription $subscription
     * @param array        $formData
     *
     * @return Metadata
     */
    private function getOriginalMetadata(Subscription $subscription, array $formData)
    {
        $metadata = new Metadata();
        $metadata->acsLocation = $subscription->getAcsLocation();
        $metadata->entityId = $subscription->getEntityId();
        $metadata->certificate = $subscription->getCertificate();

        foreach ($this->getProps() as $key => $prop) {
            $metadata->$key = array_key_exists($prop, $formData) ? $formData[$prop] : $subscription->{'get' . ucfirst(
                $prop
            )}();
        }

        foreach ($this->getContacts() as $contact) {
            $metadata->$contact = $this->getContactData($subscription, $formData, $contact);
        }

        foreach ($this->getAttributes() as $attribute) {
            $metadata->$attribute = $this->getAttributeData($subscription, $formData, $attribute);
        }

        return $metadata;
    }

    /**
     * @param Subscription $subscription
     * @param array        $formData
     * @param string       $type
     *
     * @return Contact
     */
    private function getContactData(Subscription $subscription, array $formData, $type)
    {
        /** @var Contact $orgContact */
        $orgContact = $subscription->{'get' . ucfirst($type)}();

        if ($orgContact instanceof Contact) {
            $contact = clone $orgContact;
        } else {
            $contact = new Contact();
        }

        if (array_key_exists($type, $formData)) {
            $formData = $formData[$type];

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
     * @param Subscription $subscription
     * @param array        $formData
     * @param string       $type
     *
     * @return Attribute
     */
    private function getAttributeData(Subscription $subscription, array $formData, $type)
    {
        /** @var Attribute $orgAttribute */
        $orgAttribute = $subscription->{'get' . ucfirst($type)}();

        // Only use the original values when 'validating' the metadata
        $onlyMetadataSubmitted = count($formData) === 1;

        if ($orgAttribute instanceof Attribute && $onlyMetadataSubmitted) {
            $attribute = clone $orgAttribute;
        } else {
            $attribute = new Attribute();
        }

        if (array_key_exists($type, $formData)) {
            $formData = $formData[$type];

            if (array_key_exists('requested', $formData)) {
                $attribute->setRequested(true);
            }

            if (array_key_exists('motivation', $formData)) {
                $attribute->setMotivation($formData['motivation']);
            }
        }

        return $attribute;
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

    /**
     * @return array
     */
    private function getProps()
    {
        return array(
            'logoUrl'          => 'logoUrl',
            'nameEn'           => 'nameEn',
            'nameNl'           => 'nameNl',
            'descriptionEn'    => 'descriptionEn',
            'descriptionNl'    => 'descriptionNl',
            'applicationUrlEn' => 'applicationUrl',
        );
    }

    /**
     * @return array
     */
    private function getContacts()
    {
        return array(
            'administrativeContact',
            'technicalContact',
            'supportContact'
        );
    }

    /**
     * @return array
     */
    private function getAttributes()
    {
        return array(
            'givenNameAttribute',
            'surNameAttribute',
            'commonNameAttribute',
            'displayNameAttribute',
            'emailAddressAttribute',
            'organizationAttribute',
            'organizationTypeAttribute',
            'affiliationAttribute',
            'entitlementAttribute',
            'principleNameAttribute',
            'uidAttribute',
            'preferredLanguageAttribute',
            'organizationalUnitAttribute',
            'personalCodeAttribute',
        );
    }
}
