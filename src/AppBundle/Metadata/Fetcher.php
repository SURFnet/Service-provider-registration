<?php

namespace AppBundle\Metadata;

use Doctrine\Common\Cache\Cache;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\CurlException;
use Monolog\Logger;

/**
 * Class Fetcher
 */
class Fetcher extends MetadataUtil
{
    /**
     * @var Client
     */
    private $guzzle;

    /**
     * Constructor
     *
     * @param Client $guzzle
     * @param Cache  $cache
     * @param Logger $logger
     */
    public function __construct(Client $guzzle, Cache $cache, Logger $logger)
    {
        $this->guzzle = $guzzle;

        parent::__construct($cache, $logger);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function fetch($url)
    {
        $cacheId = 'xml-' . $url;

        if (false !== $xml = $this->cache->fetch($cacheId)) {
            return $xml;
        }

        try {
            $xml = $this->guzzle->get($url, null, array('timeout' => 10, 'verify' => false))->send()->xml();
            $xml = $xml->asXML();
        } catch (CurlException $e) {
            $this->log('Metadata CURL exception', $e);

            $curlError = ' (' . $this->getCurlErrorDescription($e->getErrorNo()) . ').';

            throw new \InvalidArgumentException('Failed retrieving the metadata' . $curlError);
        } catch (\Exception $e) {
            $this->log('Metadata exception', $e);
            throw new \InvalidArgumentException('Failed retrieving the metadata.');
        }

        $this->cache->save($cacheId, $xml, 60 * 60 * 24);

        return $xml;
    }

    /**
     * @param int $errNo
     *
     * @return string
     */
    private function getCurlErrorDescription($errNo)
    {
        $error = '';
        switch ($errNo) {
            case 51:
                $error = 'SSL certificate is not valid';
                break;
            case 60:
                $error = 'SSL certificate cannot be authenticated';
                break;
        }

        if (!empty($error)) {
            $error .= ' - ';
        }

        return $error . 'code ' . $errNo;
    }
}
