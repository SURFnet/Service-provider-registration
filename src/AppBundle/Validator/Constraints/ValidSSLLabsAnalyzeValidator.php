<?php

namespace AppBundle\Validator\Constraints;

use SURFnet\SslLabs\Service\GradeComparatorService;
use SURFnet\SslLabs\Service\SynchronousAnalyzeService;
use SURFnet\SslLabs\Service\ValidateRequiredGradeService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidSSLLabsAnalyzeValidator extends ConstraintValidator
{
    /**
     * @var SynchronousAnalyzeService
     */
    private $analyzeService;

    /**
     * @var GradeComparatorService
     */
    private $gradeComparator;

    /**
     * @var string
     */
    private $passingGrade;

    /**
     * ValidSSLLabsAnalyzeValidator constructor.
     * @param SynchronousAnalyzeService $analyzeService
     * @param GradeComparatorService $gradeComparator
     * @param string $passingGrade
     */
    public function __construct(
        SynchronousAnalyzeService $analyzeService,
        GradeComparatorService $gradeComparator,
        $passingGrade
    ) {
        $this->analyzeService = $analyzeService;
        $this->gradeComparator = $gradeComparator;
        $this->passingGrade = $passingGrade;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $protocol = strtolower(parse_url($value, PHP_URL_SCHEME));

        if ($protocol !== 'https') {
            return;
        }

        $hostname = parse_url($value, PHP_URL_HOST);

        $hostDto = $this->analyzeService->analyze($hostname);

        $validated = true;
        foreach ($hostDto->endpoints as $endpoint) {
            $validated = $validated && $this->gradeComparator->isHigherThan(
                $endpoint->grade,
                $this->passingGrade
            );
        }

        if ($validated) {
            return;
        }

        $this->context->addViolation(
            "At least a {$this->passingGrade} is required from SSL Labs, "
            . "however not all server configurations match this, "
            . "please see: https://www.ssllabs.com/ssltest/analyze.html?d="
            . $hostname
            . ' . Use SSL Labs "Clear cache" on this URL to retry'
            . ' after making server configurations.'
        );
    }
}
