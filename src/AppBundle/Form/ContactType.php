<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ContactType
 */
class ContactType extends AbstractType
{
    /**
     * @var bool
     */
    private $includeEmail = true;

    /**
     * @param bool $includeEmail
     */
    public function __construct($includeEmail = true)
    {
        $this->includeEmail = $includeEmail;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName');

        if ($this->includeEmail) {
            $builder->add('email');
        }

        $builder->add('phone');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'AppBundle\Model\Contact'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'contact';
    }
}
