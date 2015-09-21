<?php

namespace SURFnet\SPRegistration\Entity;

use AppBundle\Entity\Subscription;
use OpenConext\JanusClient\ConnectionAccess;
use OpenConext\JanusClient\Entity\Connection;
use SAML2_Certificate_X509;
use SURFnet\SPRegistration\Janus\ArpMapper;
use SURFnet\SPRegistration\Janus\ContactMapper;
use SURFnet\SPRegistration\Janus\MetadataMapper;
use SURFnet\SPRegistration\ServiceRegistry\Constants as ServiceRegistry;

/**
 * Translate a ConnectionRequest to a Connection and back.
 *
 * @package SURFnet\SPRegistration\Entity
 */
final class ConnectionRequestTranslator
{
    /**
     * @var ArpMapper
     */
    private $arpMapper;

    /**
     * @var ContactMapper
     */
    private $contactMapper;

    /**
     * @var MetadataMapper
     */
    private $metadataMapper;

    /**
     * ConnectionRequestTranslator constructor.
     * @param ArpMapper $arpMapper
     * @param ContactMapper $contactMapper
     * @param MetadataMapper $metadataMapper
     */
    public function __construct(
        ArpMapper $arpMapper,
        ContactMapper $contactMapper,
        MetadataMapper $metadataMapper
    ) {
        $this->arpMapper = $arpMapper;
        $this->contactMapper = $contactMapper;
        $this->metadataMapper = $metadataMapper;
    }


    /**
     * Translate a Connection Request to an actual Janus Connection.
     *
     * @param Subscription $request
     *   Connection request.
     *
     * @return Connection
     *   Janus Connection based on request.
     */
    public function translateToConnection(Subscription $request)
    {
        return new Connection(
            $request->getEntityId(),
            Connection::TYPE_SP,
            Connection::WORKFLOW_TEST,
            $this->metadataMapper->mapRequestToMetadata($request),
            $request->getMetadataUrl(),
            '',
            new ConnectionAccess(),
            $this->arpMapper->mapRequestToArpAttributes($request)
        );
    }

    /**
     * Update a Connection Request with a Janus Connection.
     *
     * @param Connection $connection
     *   Janus connection.
     * @param Subscription $request
     *   Connection Request to update.
     */
    public function translateFromConnection(
        Connection $connection,
        Subscription $request
    ) {
        $request->setNameNl($connection->getMetadata(ServiceRegistry::NAME_NL));
        $request->setNameEn($connection->getMetadata(ServiceRegistry::NAME_EN));
        $request->setDescriptionEn($connection->getMetadata(ServiceRegistry::DESCRIPTION_EN));
        $request->setDescriptionNl($connection->getMetadata(ServiceRegistry::DESCRIPTION_NL));
        $request->setApplicationUrl($connection->getMetadata(ServiceRegistry::URL_EN));
        $request->setEulaUrl($connection->getMetadata(ServiceRegistry::COIN_EULA));
        $request->setLogoUrl($connection->getMetadata(ServiceRegistry::LOGO_0_URL));
        $request->setAdministrativeContact(
            $this->contactMapper->mapToContactOfType(
                ServiceRegistry::CONTACT_TYPE_ADMINISTRATIVE,
                $connection
            )
        );
        $request->setSupportContact(
            $this->contactMapper->mapToContactOfType(
                ServiceRegistry::CONTACT_TYPE_SUPPORT,
                $connection
            )
        );
        $request->setTechnicalContact(
            $this->contactMapper->mapToContactOfType(
                ServiceRegistry::CONTACT_TYPE_TECHNICAL,
                $connection
            )
        );
        $request->setAcsLocation(
            $connection->getMetadata(ServiceRegistry::ASSERTIONCONSUMERSERVICE_0_LOCATION)
        );
        $certData = $connection->getMetadata('certData');
        if ($certData) {
            $request->setCertificate(
                SAML2_Certificate_X509::createFromCertificateData($certData)->getCertificate()
            );
        }
    }
}
