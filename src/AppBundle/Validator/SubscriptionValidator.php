<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Subscription;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SubscriptionValidator
 * @package AppBundle\Validator
 */
final class SubscriptionValidator
{
    /**
     * @param int $status
     * @return $this
     */
    public function isOfStatus($status)
    {
        if ($this->subscription->getStatus() === $status) {
            return $this;
        }

        throw new BadRequestHttpException(
            sprintf(
                'Subscription "%s" is of status "%s" expected status "%s"',
                $this->subscription->getId(),
                $this->subscription->getStatus(),
                $status
            )
        );
    }

    /**
     * @param $environment
     * @return $this
     */
    public function isForEnvironment($environment)
    {
        if ($this->subscription->getEnvironment() === $environment) {
            return $this;
        }

        throw new BadRequestHttpException(
            sprintf(
                'Subscription "%s" is of status "%s" expected status "%s"',
                $this->subscription->getId(),
                $this->subscription->getEnvironment(),
                $environment
            )
        );
    }

    /**
     * @param Subscription $subscription
     * @return static
     */
    public static function create(Subscription $subscription)
    {
        return new static($subscription);
    }

    /**
     * SubscriptionValidator constructor.
     * @param Subscription $subscription
     */
    private function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * @var Subscription
     */
    private $subscription;
}
