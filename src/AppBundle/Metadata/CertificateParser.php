<?php

namespace AppBundle\Metadata;

/**
 * Class CertificateParser
 */
class CertificateParser
{
    /**
     * @param string $certificateString
     *
     * @return string
     */
    public function parse($certificateString)
    {
        $certificateString = str_replace('-----BEGIN CERTIFICATE-----', '', $certificateString);
        $certificateString = str_replace('-----END CERTIFICATE-----', '', $certificateString);
        $certificateString = str_replace(array("\n", "\r", " ", "\t"), '', $certificateString);
        $certificateString = chunk_split($certificateString, 64, PHP_EOL);

        return "-----BEGIN CERTIFICATE-----" . PHP_EOL . $certificateString . "-----END CERTIFICATE-----";
    }

    /**
     * @param string $certificate
     *
     * @return string
     */
    public function getSubject($certificate)
    {
        $certificateInfo = openssl_x509_parse($certificate);

        return $certificateInfo['name'];
    }
}
