<?php


namespace AppBundle\Form;


use AppBundle\Entity\Subscription;
use AppBundle\Manager\LockManager;
use AppBundle\Metadata\Parser;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SubscriptionTypeFactory
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var LockManager
     */
    private $lockManager;

    /**
     * @var Parser
     */
    private $metadataParser;

    /**
     * SubscriptionTypeFactory constructor.
     * @param FormFactory $formFactory
     * @param LockManager $lockManager
     * @param Parser $metadataParser
     */
    public function __construct(
        FormFactory $formFactory,
        LockManager $lockManager,
        Parser $metadataParser
    ) {
        $this->formFactory = $formFactory;
        $this->lockManager = $lockManager;
        $this->metadataParser = $metadataParser;
    }

    public function buildForm(
        Subscription $subscription,
        Request $request,
        $csrfProtection = true
    ) {
        $form = new SubscriptionType(
            $this->metadataParser,
            $request->getSession()
        );

        $formOptions = array(
            'disabled'        => !$this->lockManager->lock($subscription->getId()),
            'csrf_protection' => $csrfProtection,
        );

        $requestedState = $request->get('subscription[requestedState]', null, true);
        if ($requestedState === 'finished') {
            $formOptions['validation_groups'] = array('Default', 'finished');
        }

        return $this->formFactory->create($form, $subscription, $formOptions);
    }
}
