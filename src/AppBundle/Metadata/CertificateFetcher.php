<?php

namespace AppBundle\Metadata;

use Doctrine\Common\Cache\Cache;

/**
 * Class CertificateFetcher
 */
class CertificateFetcher
{
    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function fetch($url)
    {
        $context = stream_context_create(
            array(
                'ssl'  =>
                    array('capture_peer_cert' => true),
                'http' =>
                    array('ignore_errors' => true)
            )
        );

        $res = @fopen($url, "rb", false, $context);

        if ($res === false) {
            throw new \InvalidArgumentException('Unable to connect.');
        }

        $cont = stream_context_get_params($res);

        if (!isset($cont['options']['ssl']['peer_certificate'])) {
            throw new \InvalidArgumentException('Unable to retrieve SSL certificate.');
        }

        if (!openssl_x509_export($cont['options']['ssl']['peer_certificate'], $cert, true)) {
            throw new \InvalidArgumentException('Unable to parse SSL certificate.');
        }

        return $cert;
    }
}
