<?php

namespace SURFnet\SPRegistration\Service;

use AppBundle\Entity\DoctrineSubscriptionRepository;
use AppBundle\Entity\Subscription;
use OpenConext\JanusClient\Entity\ConnectionDescriptorRepository;
use OpenConext\JanusClient\Entity\ConnectionRepository;
use OpenConext\JanusClient\NewConnectionRevision;
use RuntimeException;
use SURFnet\SPRegistration\Entity\ConnectionRequestTranslator;

/**
 * Class JanusSyncService
 *
 * @package SURFnet\SPRegistration\Service
 */
class JanusSyncService
{
    /**
     * @param Subscription $currentSubscription
     * @return Subscription
     */
    public function pull(Subscription $currentSubscription)
    {
        // Ignore Requests that are not published.
        if (!$currentSubscription->isPublished()) {
            return $currentSubscription;
        }

        if (!$currentSubscription->getJanusId()) {
            return $currentSubscription;
        }

        // Otherwise we update our database (cache) with the data from Janus.
        $connection = $this->janusConnectionRepository->fetchById(
            $currentSubscription->getJanusId()
        );

        $newSubscription = $this->translator->translateFromConnection(
            $connection,
            $currentSubscription
        );

        return $this->repository->update(
            $currentSubscription,
            $newSubscription
        );
    }

    /**
     * @param Subscription $subscription
     * @return Subscription
     */
    public function push(Subscription $subscription)
    {
        // Ignore Requests that are not published.
        if (!$subscription->isPublished()) {
            return $subscription;
        }

        if (!$subscription->getJanusId()) {
            $this->insertInJanus($subscription);
        } else {
            $this->updateInJanus($subscription);
        }

        return $subscription;
    }

    /**
     * @param Subscription $subscription
     * @return Subscription
     */
    private function insertInJanus(Subscription $subscription)
    {
        // Find the entityId in Janus.
        $entityId = $subscription->getEntityId();
        $descriptor = $this->janusConnectionDescriptorRepository->findByName(
            $entityId
        );

        if ($descriptor) {
            throw new RuntimeException("Entity $entityId already in Janus");
        }

        $connection = $this->janusConnectionRepository->insert(
            new NewConnectionRevision(
                $this->translator->translateToConnection($subscription),
                'Created entity from intakeform id: ' . $subscription->getId()
            )
        );

        return $this->repository->update(
            clone $subscription,
            $subscription->setJanusId($connection->getId())
        );
    }

    /**
     * @param Subscription $subscription
     */
    private function updateInJanus(Subscription $subscription)
    {
        return $this->janusConnectionRepository->update(
            new NewConnectionRevision(
                $this->translator->translateToConnection($subscription),
                'Updated entity from intakeform id: ' . $subscription->getId()
            )
        );
    }

    /**
     * JanusSyncService constructor.
     *
     * @param DoctrineSubscriptionRepository $repository
     * @param ConnectionDescriptorRepository $janusConnectionDescriptorRepository
     * @param ConnectionRepository           $janusConnectionRepository
     * @param ConnectionRequestTranslator    $translator
     */
    public function __construct(
        DoctrineSubscriptionRepository $repository,
        ConnectionDescriptorRepository $janusConnectionDescriptorRepository,
        ConnectionRepository $janusConnectionRepository,
        ConnectionRequestTranslator $translator
    ) {
        $this->repository = $repository;
        $this->janusConnectionDescriptorRepository = $janusConnectionDescriptorRepository;
        $this->janusConnectionRepository = $janusConnectionRepository;
        $this->translator = $translator;
    }

    /**
     * @var DoctrineSubscriptionRepository
     */
    private $repository;

    /**
     * @var ConnectionDescriptorRepository
     */
    private $janusConnectionDescriptorRepository;

    /**
     * @var ConnectionRepository
     */
    private $janusConnectionRepository;

    /**
     * @var ConnectionRequestTranslator
     */
    private $translator;
}
