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
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $lockTime;

    /**
     * Constructor
     *
     * @param Cache   $cache
     * @param Session $session
     * @param int     $defaultLockTime
     */
    public function __construct(Cache $cache, Session $session, $defaultLockTime = 12)
    {
        $this->cache = $cache;
        $this->sessionId = $session->getId();
        $this->lockTime = $defaultLockTime;
    }

    /**
     * @param string $id
     *
     * @return bool
     * @todo: this is not atomic!
     */
    public function lock($id)
    {
        $cacheId = 'lock-' . $id;

        $lock = $this->cache->fetch($cacheId);

        // If there already is a lock for another session -> fail.
        if ($lock !== false && $lock !== $this->sessionId) {
            return false;
        }

        return $this->cache->save($cacheId, $this->sessionId, $this->lockTime);
    }
}
