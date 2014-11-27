<?php
namespace AppBundle\Validator\Constraints;

use Guzzle\Common\Exception\GuzzleException;
use Guzzle\Http\Client;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidSSLCertificateValidator
 */
class ValidSSLCertificateValidator extends ConstraintValidator
{
    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @param Client $guzzle
     */
    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
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
            $this->context->addViolation('Invalid key length');

            return;
        }

        return;

        // @todo: fix this
        // $acsLocation = $this->context->getRoot()->getData()->getAcsLocation();
        //
        // try {
        //     $response = $this->guzzle->get($acsLocation)->send();
        // } catch (GuzzleException $e) {
        //     return;
        // }
        //
        // openssl_x509_export($cont['options']['ssl']['peer_certificate'], $acsCert, true);
        //
        // if ($value === $acsCert) {
        //     $this->context->addViolation('Certificate matches certificate of ACSLocation which is not allowed.');
        // }
    }
}
