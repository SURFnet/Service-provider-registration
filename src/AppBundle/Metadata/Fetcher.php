<?php

namespace AppBundle\Metadata;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\CurlException;
use Monolog\Logger;

/**
 * Class Fetcher
 */
class Fetcher
{
    /**
     * @var Client
     */
    private $guzzle;

    /**
     * Constructor
     *
     * @param Client $guzzle
     * @param Logger $logger
     */
    public function __construct(Client $guzzle, Logger $logger)
    {
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function fetch($url)
    {
        try {
            $xml = $this->guzzle->get($url, null, array('timeout' => 10, 'verify' => false))->send()->xml();
            $xml = $xml->asXML();
        } catch (CurlException $e) {
            $this->logger->addInfo('Metadata CURL exception', array('e' => $e));

            $curlError = ' (' . $this->getCurlErrorDescription($e->getErrorNo()) . ').';

            throw new \InvalidArgumentException('Failed retrieving the metadata' . $curlError);
        } catch (\Exception $e) {
            $this->logger->addInfo('Metadata exception', array('e' => $e));
            throw new \InvalidArgumentException('Failed retrieving the metadata.');
        }

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
