<?php

/**
 * Class LockManagerTest
 */
class LockManagerTest extends PHPUnit_Framework_TestCase
{
    public function testLocking()
    {
        $cache = new \Doctrine\Common\Cache\FilesystemCache('/tmp/spformtest');

        $session1 = new \Symfony\Component\HttpFoundation\Session\Session(
            new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage('S1')
        );
        $session1->start();

        $session2 = new \Symfony\Component\HttpFoundation\Session\Session(
            new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage('S2')
        );
        $session2->start();

        $lockManager1 = new \AppBundle\Manager\LockManager($cache, $session1);
        $lockManager2 = new \AppBundle\Manager\LockManager($cache, $session2);

        // Test successful locking
        $this->assertTrue($lockManager1->lock(1));
        $this->assertTrue($lockManager1->lock(1));
        sleep(15);
        $this->assertTrue($lockManager1->lock(1));

        // Test failed from another session
        $this->assertTrue($lockManager1->lock(1));
        $this->assertFalse($lockManager2->lock(1));

        // Test successful lock after lock expired
        sleep(15);
        $this->assertTrue($lockManager2->lock(1));

        // Test locking of different resources
        $this->assertTrue($lockManager1->lock(3));
        $this->assertTrue($lockManager1->lock(4));
    }
}
