<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\CertificateFetcher;
use AppBundle\Metadata\CertificateParser;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidSSLCertificateValidator
 */
class ValidSSLCertificateValidator extends ConstraintValidator
{
    /**
     * @var CertificateFetcher
     */
    private $fetcher;

    /**
     * @var CertificateParser
     */
    private $parser;

    /**
     * Constructor
     *
     * @param CertificateFetcher $fetcher
     * @param CertificateParser  $parser
     */
    public function __construct(CertificateFetcher $fetcher, CertificateParser $parser)
    {
        $this->fetcher = $fetcher;
        $this->parser = $parser;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        $cert = openssl_x509_parse($value);

        if ($cert === false) {
            $this->context->addViolation($constraint->message);

            return;
        }

        openssl_x509_export($value, $cert, false);

        if (!preg_match('~(\d+) bit~', $cert, $matches)) {
            $this->context->addViolation('Cannot determine key length');

            return;
        }

        if ($matches[1] < 2048) {
            $this->context->addViolation(
                'Key length is %length% bit, it should be 2048 bit or more.',
                array(
                    '%length%' => $matches[1]
                )
            );

            return;
        }
    }
}
