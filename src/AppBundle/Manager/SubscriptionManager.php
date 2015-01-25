<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Subscription;
use AppBundle\Metadata\Generator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ValidatorInterface;

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
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param ValidatorInterface $validator
     * @param LockManager $lockManager
     * @param Generator $generator
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator,
        LockManager $lockManager,
        Generator $generator
    ) {
        $this->em = $entityManager;
        $this->repo = $entityManager->getRepository('AppBundle:Subscription');
        $this->validator = $validator;
        $this->lockManager = $lockManager;
        $this->generator = $generator;
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
    }

    /**
     * @param Subscription $subscription
     */
    public function updateSubscription(Subscription $subscription)
    {
        $this->em->flush($subscription);
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
     * @param Subscription $subscription
     *
     * @return string
     */
    public function generateMetadata(Subscription $subscription)
    {
        if (!$subscription->isFinished()) {
            throw new \InvalidArgumentException('Subscription should be finished before generating the Metadata.');
        }

        return $this->generator->generate($subscription);
    }
}
