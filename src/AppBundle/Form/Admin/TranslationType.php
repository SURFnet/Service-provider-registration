<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Translation form type.
 */
class TranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('locale', 'hidden');
        $builder->add(
            'content',
            'purified_textarea',
            array(
                'required' => false,
                'label'    => false,
                'attr'     => array('class' => 'tinymce')
            )
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $translation = $event->getForm()->getParent()->getParent()->getData();
                $form = $event->getForm();

                if (!preg_match('~intro|help~', $translation->getKey())) {
                    $form->add('content', 'textarea', array('required' => false, 'label' => false));
                }
            }
        );

    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label'] = $form['locale']->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => null,
                'translation_domain' => 'LexikTranslationBundle'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lxk_translation';
    }
}
