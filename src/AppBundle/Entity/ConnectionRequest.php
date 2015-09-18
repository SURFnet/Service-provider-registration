<?php

namespace AppBundle\Entity;

use AppBundle\Model\Attribute;
use AppBundle\Model\Contact;
use AppBundle\Model\Saml2Metadata;
use AppBundle\Model\UiMetadata;

class ConnectionRequest {
  /**
   * @var string
   */
  private $id;

  /**
   * @var string
   */
  private $ticketNumber;

  /**
   * @var string
   */
  private $email;

  /**
   * @var ConnectionRequestContacts
   */
  private $contacts;

  /**
   * @var Saml2Metadata
   */
  private $saml2Metadata;

  /**
   * @var UiMetadata
   */
  private $uiMetadata;

  /**
   * @var Attribute[]
   */
  private $attributes;

  /**
   * @var string
   */
  private $comments;

  /**
   * ConnectionRequest constructor.
   * @param string $id
   * @param string $ticketNumber
   * @param string $email
   * @param Contact[] $contacts
   * @param Saml2Metadata $saml2Metadata
   * @param UiMetadata $uiMetadata
   * @param Attribute[] $attributes
   * @param string $comments
   */
  public function __construct(
    $id,
    $ticketNumber,
    $email,
    ConnectionRequestContacts $contacts,
    Saml2Metadata $saml2Metadata,
    UiMetadata $uiMetadata,
    array $attributes,
    $comments
  ) {
    $this->id = $id;
    $this->ticketNumber = $ticketNumber;
    $this->email = $email;
    $this->contacts = $contacts;
    $this->saml2Metadata = $saml2Metadata;
    $this->uiMetadata = $uiMetadata;
    $this->attributes = $attributes;
    $this->comments = $comments;
  }


}
