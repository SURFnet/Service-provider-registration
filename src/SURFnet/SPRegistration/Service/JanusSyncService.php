<?php

namespace SURFnet\SPRegistration\Service;

use AppBundle\Entity\Subscription;
use AppBundle\Manager\SubscriptionManager;
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
     * @param Subscription $subscription
     */
    public function pull(Subscription $subscription)
    {
        // Ignore Requests that are not published.
        if (!$subscription->isPublished()) {
            return;
        }

        if (!$subscription->getJanusId()) {
            return;
        }

        // Otherwise we update our database (cache) with the data from Janus.
        $connection = $this->janusConnectionRepository->fetchById(
            $subscription->getJanusId()
        );

        $subscription = $this->translator->translateFromConnection(
            $connection,
            $subscription
        );

        $this->repository->updateSubscription($subscription);
    }

    /**
     * @param Subscription $subscription
     */
    public function push(Subscription $subscription)
    {
        // Ignore Requests that are not published.
        if (!$subscription->isPublished()) {
            return;
        }

        if (!$subscription->getJanusId()) {
            $this->insertInJanus($subscription);
        } else {
            $this->updateInJanus($subscription);
        }
    }

    /**
     * @param Subscription $subscription
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

        $subscription->setJanusId($connection->getId());

        $this->repository->updateSubscription($subscription);
    }

    /**
     * @param Subscription $subscription
     */
    private function updateInJanus(Subscription $subscription)
    {
        $this->janusConnectionRepository->update(
            new NewConnectionRevision(
                $this->translator->translateToConnection($subscription),
                'Updated entity from intakeform id: ' . $subscription->getId()
            )
        );
    }

    /**
     * JanusSyncService constructor.
     *
     * @param SubscriptionManager            $repository
     * @param ConnectionDescriptorRepository $janusConnectionDescriptorRepository
     * @param ConnectionRepository           $janusConnectionRepository
     * @param ConnectionRequestTranslator    $translator
     */
    public function __construct(
        SubscriptionManager $repository,
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
     * @var SubscriptionManager
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
