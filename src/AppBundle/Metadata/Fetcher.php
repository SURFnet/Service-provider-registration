<?php

namespace AppBundle\Metadata;

use Doctrine\Common\Cache\Cache;
use Guzzle\Http\Client;
use Monolog\Logger;

/**
 * Class Fetcher
 */
class Fetcher extends Util
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
            $xml = $this->guzzle->get($url, null, array('timeout' => 10))->send()->xml();
            $xml = $xml->asXML();
        } catch (\Exception $e) {
            $this->logger->addInfo('Metadata exception', array('context' => $e));
            throw new \InvalidArgumentException('Failed retrieving the metadata.');
        }

        $this->cache->save($cacheId, $xml, 60 * 60 * 24);

        return $xml;
    }
}
