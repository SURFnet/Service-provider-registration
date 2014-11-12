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
            ->add('acsLocation', null, array('disabled' => true))
            ->add('entityId', null, array('disabled' => true))
            ->add('certificate', null, array('disabled' => true))
            ->add('logoUrl')
            ->add('nameEn')
            ->add('descriptionEn')
            ->add('nameNl')
            ->add('descriptionNl')
            ->add('applicationUrl')

            ->add('administrativeContact', new ContactType(), array('by_reference' => false))
            ->add('technicalContact', new ContactType(), array('by_reference' => false))
            ->add('supportContact', new ContactType(), array('by_reference' => false))

            ->add('givenNameAttribute', new AttributeType())
            ->add('surNameAttribute', new AttributeType())

            ->add('comments');

        $builder->get('metadataUrl')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $metadataUrl = $event->getForm()->getData();

                if (!empty($metadataUrl)) {
                    // @todo: retrieve and parse the meta data
                    $event->getForm()->getParent()->getData()->setAcsLocation('test');
                    $event->getForm()->getParent()->getData()->setEntityId('test');
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