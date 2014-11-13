<?php

namespace AppBundle\Form;

use AppBundle\Entity\Subscription;
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
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact', new ContactType(), array('by_reference' => false))

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

            ->add('administrativeContact', new ContactType(), array('by_reference' => false))
            ->add('technicalContact', new ContactType(), array('by_reference' => false))
            ->add('supportContact', new ContactType(), array('by_reference' => false))

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

            ->add('comments');

        $builder->get('metadataUrl')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $metadataUrl = $event->getForm()->getData();

                if (!empty($metadataUrl)) {
                    $subscription = $event->getForm()->getParent()->getData();

                    // @todo: retrieve, parse and pre fill the meta data
                    $subscription->setAcsLocation('ssl://www.google.com');
                    $subscription->setEntityId('https://www.test.com');
                    $subscription->setCertificate('q');
                }
            }
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
}
