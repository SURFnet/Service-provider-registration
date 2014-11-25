<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidTwigTemplateValidator
 */
class ValidTwigTemplateValidator extends ConstraintValidator
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Constructor
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        try {
            $this->twig->parse($this->twig->tokenize($value));
        } catch (\Twig_Error_Syntax $e) {
            $this->context->addViolation($e->getMessage());
        }
    }
}
