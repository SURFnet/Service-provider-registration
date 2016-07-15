<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add('ticketNo')
            ->add('contact', new ContactType(), array('by_reference' => false))
            ->add(
                'environment',
                'choice',
                array(
                    'choices'  => array('connect' => 'Connect', 'production' => 'Production'),
                    'expanded' => true,
                )
            )
            ->add(
                'locale',
                'choice',
                array(
                    'choices'  => array('en' => 'English', 'nl' => 'Dutch'),
                    'expanded' => true
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
                'data_class'        => 'AppBundle\Entity\Subscription',
                'validation_groups' => array('creation')
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_subscription';
    }
}
