<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class SubscriptionStatusChange
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SubscriptionStatusChangeRepository")
 */
class SubscriptionStatusChange
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="guid")
     */
    private $subscriptionId;

    /**
     * @var int
     * @ORM\Column(type="integer",nullable=true)
     */
    private $fromStatus = NULL;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $toStatus;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * SubscriptionStatusChange constructor.
     * @param string $id
     * @param string $subscriptionId
     * @param string $fromStatus
     * @param string $toStatus
     * @param DateTime $createdAt
     */
    public function __construct(
        $subscriptionId,
        $fromStatus,
        $toStatus,
        DateTime $createdAt = null,
        $id = null
    ) {
        $this->id = $id;
        $this->subscriptionId = $subscriptionId;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
        $this->createdAt = $createdAt;
    }
}
