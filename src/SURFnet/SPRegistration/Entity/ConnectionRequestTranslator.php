<?php

use AppBundle\Entity\Subscription;
use OpenConext\JanusClient\Entity\Connection;

class ConnectionRequestDisassembler {
  public function translateToConnection(Subscription $request) {
    return new Connection(
      $request->getEntityId(),
      Connection::TYPE_SP,
      $request->getWorkflowState(),
      $this->getMetadataFromRequest($request),
      $request->getMetadataUrl()
    );
  }

  public function translateFromConnection(
    Connection $connection,
    Subscription $request
  ) {
    $
  }
}
