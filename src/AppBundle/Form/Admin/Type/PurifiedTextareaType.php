<?php

namespace AppBundle\Form\Admin\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PurifiedTextareaType
 */
class PurifiedTextareaType extends AbstractType
{
    /**
     * @var DataTransformerInterface
     */
    private $purifierTransformer;

    /**
     * Constructor
     *
     * @param DataTransformerInterface $purifierTransformer
     */
    public function __construct(DataTransformerInterface $purifierTransformer)
    {
        $this->purifierTransformer = $purifierTransformer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->purifierTransformer);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'textarea';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'purified_textarea';
    }
}
