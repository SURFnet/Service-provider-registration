<?php

namespace AppBundle\Request;

use AppBundle\Entity\SubscriptionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SubscriptionParamConverter
 * @package AppBundle\Request
 */
class SubscriptionParamConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $subscriptionId = $request->get('id');
        $subscription = $this->subscriptionRepository->findById($subscriptionId);

        if (!$subscription) {
            throw new BadRequestHttpException(
                sprintf(
                    'Subscription with id "%s" not found',
                    $subscriptionId
                )
            );
        }

        $request->attributes->set($configuration->getName(), $subscription);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if ($configuration->getConverter() === $this->converterName) {
            return true;
        }

        return false;
    }

    /**
     * SubscriptionParamConverter constructor.
     * @param string $converterName
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct($converterName, SubscriptionRepository $subscriptionRepository)
    {
        $this->converterName = $converterName;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @var string
     */
    private $converterName;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;
}
