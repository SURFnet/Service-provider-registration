<?php

namespace AppBundle\Manager;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class LockManager
 */
class LockManager
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param Cache   $cache
     * @param Session $session
     */
    public function __construct(Cache $cache, Session $session)
    {
        $this->cache = $cache;
        $this->session = $session;
    }

    /**
     * @param string $id
     *
     * @return bool
     * @todo: this is not atomic!
     */
    public function getLock($id)
    {
        $cacheId = 'lock-' . $id;
        $sessionId = $this->session->getId();

        $lock = $this->cache->fetch($cacheId);

        // If there already is a lock for another session -> fail.
        if ($lock !== false && $lock !== $sessionId) {
            return false;
        }

        return $this->cache->save($cacheId, $sessionId, 12);
    }
}
