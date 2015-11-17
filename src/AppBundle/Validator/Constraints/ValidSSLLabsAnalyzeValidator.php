<?php

namespace AppBundle\Validator\Constraints;

use SURFnet\SslLabs\Dto\Host;
use SURFnet\SslLabs\Service\AnalyzeServiceInterface;
use SURFnet\SslLabs\Service\GradeComparatorService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ExecutionContextInterface;

class ValidSSLLabsAnalyzeValidator extends ConstraintValidator
{
    /**
     * @var AnalyzeServiceInterface
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
     * @var Host|null
     */
    private $lastHostDto = null;

    /**
     * @var null
     */
    private $lastViolation = null;

    /**
     * ValidSSLLabsAnalyzeValidator constructor.
     * @param AnalyzeServiceInterface $analyzeService
     * @param GradeComparatorService $gradeComparator
     * @param string $passingGrade
     */
    public function __construct(
        AnalyzeServiceInterface $analyzeService,
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
        $this->lastViolation = null;

        $protocol = strtolower(parse_url($value, PHP_URL_SCHEME));

        if ($protocol !== 'https') {
            return;
        }

        $hostname = parse_url($value, PHP_URL_HOST);

        $hostDto = $this->analyzeService->analyze($hostname);
        $this->lastHostDto = $hostDto;

        $endStatuses = array(Host::STATUS_ERROR, Host::STATUS_READY);
        if (!in_array($hostDto->status, $endStatuses)) {
            return;
        }

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

        $violation = "At least a {$this->passingGrade} is required from SSL Labs, "
            . "however not all server configurations match this, "
            . "please see: https://www.ssllabs.com/ssltest/analyze.html?d="
            . $hostname
            . ' . Use SSL Labs "Clear cache" on this URL to retry'
            . ' after making server configurations.';

        if ($this->context) {
            $this->context->addViolation($violation);
        }
        $this->lastViolation = $violation;
    }

    /**
     * @return Host
     */
    public function getLastHostDto()
    {
        return $this->lastHostDto;
    }

    /**
     * @return null
     */
    public function getLastViolation()
    {
        return $this->lastViolation;
    }
}
