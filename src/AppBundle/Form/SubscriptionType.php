<?php

namespace AppBundle\Form;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\Fetcher;
use AppBundle\Metadata\Parser;
use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use AppBundle\Model\Metadata;
use Exception;
use SURFnet\SPRegistration\ImageDimensions;
use SURFnet\SPRegistration\Service\TransparantImageResizeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\SubmitButton;
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
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TransparantImageResizeService
     */
    private $transparantImageResizeService;

    /**
     * @var Fetcher
     */
    private $fetcher;

    /**
     * @param Parser           $parser
     * @param SessionInterface $session
     */
    public function __construct(
        Fetcher $fetcher,
        Parser $parser,
        SessionInterface $session,
        TransparantImageResizeService $resizeService
    ) {
        $this->fetcher = $fetcher;
        $this->parser = $parser;
        $this->session = $session;
        $this->transparantImageResizeService = $resizeService;
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
            ->add('importUrl', 'url', array('default_protocol' => 'https'))
            ->add('import', 'submit')
            ->add('metadataUrl', 'hidden', array('required' => false))
            ->add('metadataXml', 'hidden', array('required' => false))
            // @todo: these should be disabled, but then validation is harder..
            ->add('acsLocation', null, array('read_only' => true, 'required' => false))
            ->add('entityId', null, array('read_only' => true, 'required' => false))
            ->add('certificate', null, array('read_only' => true))
            ->add('logoUrl', null, array('required' => false))
            ->add('nameEn')
            ->add('descriptionEn')
            ->add('nameNl')
            ->add('descriptionNl')
            ->add('applicationUrl')
            ->add('eulaUrl')
            ->add('requestedState', 'hidden', array("mapped" => false))
        ;

        // Tab Contact
        foreach ($this->getContactsTypeNames() as $contact) {
            $builder->add($contact, new ContactType(), array('by_reference' => false, 'required' => false));
        }

        // Tab Attributes
        foreach ($this->getAttributeFieldNames() as $attribute) {
            $builder->add($attribute, new AttributeType(), array('by_reference' => false, 'required' => false));
        }

        // Tab Comments
        $builder->add('comments');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

        $builder->get('logoUrl')->addEventListener(
            FormEvents::SUBMIT,
            array($this, 'onLogoUrlSubmit')
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $subscription = $event->getData();

        // If metadataUrl is not submitted return early
        if (!$subscription || !array_key_exists('importUrl', $subscription)) {
            return;
        }

        if ($subscription['requestedState'] !== 'import') {
            return;
        }

        $subscription['metadataUrl'] = $subscription['importUrl'];
        $subscription['metadataXml'] = '';
        try {
            $subscription['metadataXml'] = $this->fetcher->fetch($subscription['metadataUrl']);
            $metadata = $this->parser->parseXml($subscription['metadataXml']);

            $subscription = $this->mapMetadataToFormData($subscription, $metadata);

            $event->setData($subscription);
        } catch (\InvalidArgumentException $e) {
            // Exceptions are deliberately ignored because they are caught by the validator
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
        $formData = $this->mapFields($formData, $metadata);
        $formData = $this->mapContactFields($formData, $metadata);
        $formData = $this->mapAttributeFields($formData, $metadata);

        return $formData;
    }

    /**
     * @param array $formData
     * @param Metadata $metadata
     * @return array
     */
    private function mapFields(array $formData, Metadata $metadata)
    {
        $map = array(
            'acsLocation' => 'acsLocation',
            'entityId' => 'entityId',
            'certificate' => 'certificate',
            'logoUrl' => 'logoUrl',
            'nameEn' => 'nameEn',
            'nameNl' => 'nameNl',
            'descriptionEn' => 'descriptionEn',
            'descriptionNl' => 'descriptionNl',
            'applicationUrl' => 'applicationUrlEn',
        );

        foreach ($map as $fieldName => $dtoName) {
            if (!empty($formData[$fieldName])) {
                continue;
            }

            $formData[$fieldName] = $metadata->$dtoName;
        }
        return $formData;
    }

    /**
     * @param array $formData
     * @param Metadata $metadata
     * @return array
     */
    private function mapContactFields(array $formData, Metadata $metadata)
    {
        $contactMap = array(
            'firstName' => 'getFirstName',
            'lastName' => 'getLastName',
            'email' => 'getEmail',
            'phone' => 'getPhone',
        );

        foreach ($this->getContactsTypeNames() as $contacTypeName) {
            $contact = $metadata->$contacTypeName;

            if (!$contact instanceof Contact) {
                continue;
            }

            if (empty($formData[$contacTypeName])) {
                $formData[$contacTypeName] = array();
            }

            foreach ($contactMap as $fieldName => $metadataMethodName) {
                if (!empty($formData[$contacTypeName][$fieldName])) {
                    continue;
                }

                $formData[$contacTypeName][$fieldName] = $metadata->{$contacTypeName}->{$metadataMethodName}();
            }
        }
        return $formData;
    }

    /**
     * @param array $formData
     * @param Metadata $metadata
     * @return array
     */
    private function mapAttributeFields(array $formData, Metadata $metadata)
    {
        $attributesMap = array(
            'requested' => 'isRequested',
            'motivation' => 'getMotivation',
        );

        foreach ($this->getAttributeFieldNames() as $attributeFieldName) {
            $attribute = $metadata->$attributeFieldName;

            if (!$attribute instanceof Attribute) {
                continue;
            }

            if (empty($formData[$attributeFieldName])) {
                $formData[$attributeFieldName] = array();
            }
            foreach ($attributesMap as $fieldName => $dtoAccessorName) {
                if (!empty($formData[$attributeFieldName][$fieldName])) {
                    continue;
                }

                $formData[$attributeFieldName][$fieldName] = $metadata->{$attributeFieldName}->{$dtoAccessorName}();
            }
        }
        return $formData;
    }

    public function onLogoUrlSubmit(FormEvent $event)
    {
        $event->setData(
            $this->transparantImageResizeService->requireDimensions(
                $event->getData(),
                new ImageDimensions(500, 300)
            )
        );
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
    private function getContactsTypeNames()
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
    private function getAttributeFieldNames()
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
            'personalCodeAttribute',
            'eduPersonTargetedIDAttribute',
            'scopedAffiliationAttribute',
        );
    }
}
