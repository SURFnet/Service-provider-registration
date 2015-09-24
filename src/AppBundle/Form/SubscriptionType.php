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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @param Parser           $parser
     * @param SessionInterface $session
     */
    public function __construct(Parser $parser, SessionInterface $session)
    {
        $this->parser = $parser;
        $this->session = $session;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact', new ContactType(false), array('by_reference' => false))
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
            ->add('applicationUrl')
            ->add('eulaUrl');

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

        $metadataUrl = $subscription['metadataUrl'];

        /** @var Subscription $orgSubscription */
        $orgSubscription = $event->getForm()->getData();

        $sessionCacheId = $orgSubscription->getId() . '-metadataUrl';

        $previousMetadataUrl = $this->session->get($sessionCacheId, $orgSubscription->getMetadataUrl());
        $this->session->set($sessionCacheId, $metadataUrl);

        $metadata = new Metadata();

        try {
            // Only if the submitted url differs from the previously validated url, retrieve the metadata
            if ($metadataUrl != $previousMetadataUrl) {
                $metadata = $this->parser->parse($metadataUrl);
                $event->setData($this->mapMetadataToFormData($subscription, $metadata));
            }
        } catch (\InvalidArgumentException $e) {
            // Exceptions are deliberately ignored because they are caught by the validator
            $event->setData($this->mapMetadataToFormData($subscription, $metadata));
        }
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
