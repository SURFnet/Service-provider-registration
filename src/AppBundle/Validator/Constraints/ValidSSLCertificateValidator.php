<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidSSLCertificateValidator
 */
class ValidSSLCertificateValidator extends ConstraintValidator
{
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

        $acsLocation = $this->context->getRoot()->getData()->getAcsLocation();

        // @todo: catch exceptions, url must be ssl://x.y.x:443, timeout?
        $context = stream_context_create(array("ssl" => array("capture_peer_cert" => true)));
        $res = stream_socket_client($acsLocation, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        $cont = stream_context_get_params($res);

        openssl_x509_export($cont['options']['ssl']['peer_certificate'], $acsCert, true);

        if ($value === $acsCert) {
            $this->context->addViolation('Certificate matches certificate of ACSLocation which is not allowed.');
        }
    }
}
