<?php

/**
 * Class LockManagerTest
 */
class LockManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \AppBundle\Manager\LockManager
     */
    private $lockManager1;

    /**
     * @var \AppBundle\Manager\LockManager
     */
    private $lockManager2;

    public function setup()
    {
        $cache = new \Doctrine\Common\Cache\FilesystemCache('/tmp/spformtest');
        $cache->setNamespace(mktime() . rand(0, 100));

        $session1 = new \Symfony\Component\HttpFoundation\Session\Session(
            new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage('S1')
        );
        $session1->start();

        $session2 = new \Symfony\Component\HttpFoundation\Session\Session(
            new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage('S2')
        );
        $session2->start();

        $this->lockManager1 = new \AppBundle\Manager\LockManager($cache, $session1, 5);
        $this->lockManager2 = new \AppBundle\Manager\LockManager($cache, $session2, 5);
    }

    public function testSuccessfulLock()
    {
        $this->assertTrue($this->lockManager1->lock(1));
    }

    public function testSuccessfulLockForDifferentResources()
    {
        $this->assertTrue($this->lockManager1->lock(3));
        $this->assertTrue($this->lockManager1->lock(4));
        $this->assertTrue($this->lockManager1->lock(5));
    }

    public function testMultipleLocksForSameResource()
    {
        $this->assertTrue($this->lockManager1->lock(6));
        $this->assertTrue($this->lockManager1->lock(6));
        sleep(6);
        $this->assertTrue($this->lockManager1->lock(6));
    }

    public function testFailedLockForSameResource()
    {
        $this->assertTrue($this->lockManager1->lock(7));
        $this->assertFalse($this->lockManager2->lock(7));
    }

    public function testSuccessfulLockAfterLockExpired()
    {
        $this->assertTrue($this->lockManager1->lock(8));
        sleep(6);
        $this->assertTrue($this->lockManager2->lock(8));
    }
}
