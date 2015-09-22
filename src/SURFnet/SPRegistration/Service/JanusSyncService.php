<?php

namespace SURFnet\SPRegistration\Service;

use AppBundle\Entity\Subscription;
use AppBundle\Manager\SubscriptionManager;
use OpenConext\JanusClient\Entity\ConnectionDescriptor;
use OpenConext\JanusClient\Entity\ConnectionDescriptorRepository;
use OpenConext\JanusClient\Entity\ConnectionRepository;
use OpenConext\JanusClient\NewConnectionRevision;
use SURFnet\SPRegistration\Entity\ConnectionRequestTranslator;

/**
 * Class JanusSyncService
 * @package SURFnet\SPRegistration\Service
 */
class JanusSyncService
{
    /**
     * @param Subscription $request
     */
    public function sync(Subscription $request)
    {
        // Ignore Draft Connection Requests.
        if ($request->isDraft()) {
            // @todo turn me back on.
            //return;
        }

        // Find the entityId in Janus.
        $entityId = $request->getEntityId();
        $descriptor = $this->janusConnectionDescriptorRepository->fetchByName(
            $entityId
        );

        // If we couldn't find the entityId in Janus, then we create it and
        // have no need to sync any more.
        if (!$descriptor) {
            $this->insertInJanus($request);
            return;
        }

        // Otherwise we update our database (cache) with the data from Janus.
        $this->updatedInDatabase($request, $descriptor);
    }

    /**
     *
     */
    private function insertInJanus(Subscription $request)
    {
        $this->janusConnectionRepository->insert(
            new NewConnectionRevision(
                $this->translator->translateToConnection($request),
                'Created entity from intakeform id: ' . $request->getId()
            )
        );
    }

    /**
     * @param Subscription $subscription
     * @param ConnectionDescriptor $descriptor
     */
    private function updatedInDatabase(
        Subscription $subscription,
        ConnectionDescriptor $descriptor
    ) {
        $connection = $this->janusConnectionRepository->fetchById(
            $descriptor->getId()
        );

        $subscription = $this->translator->translateFromConnection(
            $connection,
            $subscription
        );

        $this->repository->updateSubscription($subscription);
    }

    /**
     * JanusSyncService constructor.
     * @param SubscriptionManager $repository
     * @param ConnectionDescriptorRepository $janusConnectionDescriptorRepository
     * @param ConnectionRepository $janusConnectionRepository
     * @param ConnectionRequestTranslator $translator
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
