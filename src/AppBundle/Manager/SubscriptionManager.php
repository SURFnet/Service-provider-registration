<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Subscription;
use AppBundle\Event\SubscriptionEvent;
use AppBundle\Metadata\Generator;
use AppBundle\SubscriptionEvents;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Class SubscriptionManager
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EntityRepository
     */
    private $repo;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LockManager;
     */
    private $lockManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor
     *
     * @param EntityManager            $entityManager
     * @param ValidatorInterface       $validator
     * @param LockManager              $lockManager
     * @param Generator                $generator
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator,
        LockManager $lockManager,
        Generator $generator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $entityManager;
        $this->repo = $entityManager->getRepository('AppBundle:Subscription');
        $this->validator = $validator;
        $this->lockManager = $lockManager;
        $this->generator = $generator;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $id
     * @param bool   $checkStatus
     * @param bool   $checkLock
     *
     * @return Subscription
     */
    public function getSubscription($id, $checkStatus = false, $checkLock = false)
    {
        $this->dispatcher->dispatch(
            SubscriptionEvents::PRE_READ,
            new SubscriptionEvent($id)
        );

        $subscription = $this->repo->find($id);

        if (empty($subscription)) {
            return $subscription;
        }

        if ($checkLock && !$this->lockManager->lock($id)) {
            throw new \RuntimeException('Subscription is locked');
        }

        if ($checkStatus && $subscription->isFinished()) {
            throw new \InvalidArgumentException('Subscription has already been finished');
        }

        return $subscription;
    }

    /**
     * @return Subscription[]
     */
    public function getDraftSubscriptions()
    {
        return $this->repo->findBy(array('status' => Subscription::STATE_DRAFT));
    }

    /**
     * @param Subscription $subscription
     */
    public function saveSubscription(Subscription $subscription)
    {
        $this->em->persist($subscription);
        $this->em->flush($subscription);

        $this->dispatcher->dispatch(
            SubscriptionEvents::POST_WRITE,
            new SubscriptionEvent($subscription->getId(), $subscription)
        );
    }

    /**
     * @param Subscription $subscription
     */
    public function updateSubscription(Subscription $subscription)
    {
        $this->em->flush($subscription);

        $this->dispatcher->dispatch(
            SubscriptionEvents::POST_WRITE,
            new SubscriptionEvent($subscription->getId(), $subscription)
        );
    }

    /**
     * @param Subscription $subscription
     */
    public function deleteSubscription(Subscription $subscription)
    {
        $this->em->remove($subscription);
        $this->em->flush();
    }

    /**
     * @param Subscription $subscription
     *
     * @return bool
     */
    public function isValidSubscription(Subscription $subscription)
    {
        return count($this->validator->validate($subscription)) === 0;
    }

    /**
     * @param string           $id
     * @param SessionInterface $session
     *
     * @return Subscription
     */
    public function getSubscriptionFromSession($id, SessionInterface $session)
    {
        $sessionId = 'subscription-' . $id;

        $subscription = $session->get($sessionId);

        if (!$subscription instanceof Subscription) {
            throw new \InvalidArgumentException('Subscription not found in session');
        }

        return $this->em->merge($subscription);
    }

    /**
     * @param Subscription     $subscription
     * @param SessionInterface $session
     */
    public function storeSubscriptionInSession(Subscription $subscription, SessionInterface $session)
    {
        $sessionId = 'subscription-' . $subscription->getId();

        $this->em->detach($subscription);

        $session->set($sessionId, $subscription);
    }
}
