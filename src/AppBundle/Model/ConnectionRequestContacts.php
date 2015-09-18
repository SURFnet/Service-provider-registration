<?php

namespace AppBundle\Entity;

use AppBundle\Model\Contact;

class ConnectionRequestContacts {
  /**
   * @var Contact
   */
  private $general;

  /**
   * @var Contact
   */
  private $administrative;

  /**
   * @var Contact
   */
  private $technical;

  /**
   * @var Contact
   */
  private $support;

  /**
   * ConnectionRequestContacts constructor.
   * @param Contact $general
   * @param Contact $administrative
   * @param Contact $technical
   * @param Contact $support
   */
  public function __construct(
    Contact $general,
    Contact $administrative,
    Contact $technical,
    Contact $support
  ) {
    $this->general = $general;
    $this->administrative = $administrative;
    $this->technical = $technical;
    $this->support = $support;
  }

  /**
   * @return Contact
   */
  public function getGeneral() {
    return $this->general;
  }

  /**
   * @return Contact
   */
  public function getAdministrative() {
    return $this->administrative;
  }

  /**
   * @return Contact
   */
  public function getTechnical() {
    return $this->technical;
  }

  /**
   * @return Contact
   */
  public function getSupport() {
    return $this->support;
  }
}
