<?php

namespace SURFnet\SPRegistration\Janus;

use AppBundle\Model\Contact;
use OpenConext\JanusClient\Entity\Connection;
use SURFnet\SPRegistration\ServiceRegistry\Constants as ServiceRegistry;

/**
 * Class ContactMapper
 * @package SURFnet\SPRegistration\Janus
 */
final class ContactMapper
{
    /**
     * @param $contactType
     * @param Connection $connection
     * @return Contact|null
     */
    public function mapToContactOfType($contactType, Connection $connection)
    {
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_CONTACTTYPE)) {
            if ($connection->getMetadata(ServiceRegistry::CONTACTS_0_CONTACTTYPE) === $contactType) {
                return $this->getContact0($connection);
            }
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_CONTACTTYPE)) {
            if ($connection->getMetadata(ServiceRegistry::CONTACTS_1_CONTACTTYPE) === $contactType) {
                return $this->getContact1($connection);
            }
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_CONTACTTYPE)) {
            if ($connection->getMetadata(ServiceRegistry::CONTACTS_2_CONTACTTYPE) === $contactType) {
                return $this->getContact2($connection);
            }
        }
        return null;
    }

    /**
     * @param Connection $connection
     * @return Contact
     */
    private function getContact0(Connection $connection)
    {
        $contact = new Contact();

        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_GIVENNAME)) {
            $contact->setFirstName(
                $connection->getMetadata(ServiceRegistry::CONTACTS_0_GIVENNAME)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_SURNAME)) {
            $contact->setLastName(
                $connection->getMetadata(ServiceRegistry::CONTACTS_0_SURNAME)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_EMAILADDRESS)) {
            $contact->setEmail(
                $connection->getMetadata(ServiceRegistry::CONTACTS_0_EMAILADDRESS)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_0_TELEPHONENUMBER)) {
            $contact->setPhone(
                $connection->getMetadata(ServiceRegistry::CONTACTS_0_TELEPHONENUMBER)
            );
        }

        return $contact;
    }

    /**
     * @param Connection $connection
     * @return Contact
     */
    private function getContact1(Connection $connection)
    {
        $contact = new Contact();

        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_GIVENNAME)) {
            $contact->setFirstName(
                $connection->getMetadata(ServiceRegistry::CONTACTS_1_GIVENNAME)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_SURNAME)) {
            $contact->setLastName(
                $connection->getMetadata(ServiceRegistry::CONTACTS_1_SURNAME)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_EMAILADDRESS)) {
            $contact->setEmail(
                $connection->getMetadata(ServiceRegistry::CONTACTS_1_EMAILADDRESS)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_1_TELEPHONENUMBER)) {
            $contact->setPhone(
                $connection->getMetadata(ServiceRegistry::CONTACTS_1_TELEPHONENUMBER)
            );
        }

        return $contact;
    }

    /**
     * @param Connection $connection
     * @return Contact
     */
    private function getContact2(Connection $connection)
    {
        $contact = new Contact();

        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_GIVENNAME)) {
            $contact->setFirstName(
                $connection->getMetadata(ServiceRegistry::CONTACTS_2_GIVENNAME)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_SURNAME)) {
            $contact->setLastName(
                $connection->getMetadata(ServiceRegistry::CONTACTS_2_SURNAME)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_EMAILADDRESS)) {
            $contact->setEmail(
                $connection->getMetadata(ServiceRegistry::CONTACTS_2_EMAILADDRESS)
            );
        }
        if ($connection->hasMetadata(ServiceRegistry::CONTACTS_2_TELEPHONENUMBER)) {
            $contact->setPhone(
                $connection->getMetadata(ServiceRegistry::CONTACTS_2_TELEPHONENUMBER)
            );
        }

        return $contact;
    }
}
