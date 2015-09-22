<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Subscription;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MailManager
 */
class MailManager
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor
     *
     * @param \Swift_Mailer       $mailer
     * @param EngineInterface     $templating
     * @param TranslatorInterface $translator
     * @param string              $sender
     * @param string              $receiver
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        TranslatorInterface $translator,
        $sender,
        $receiver
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->translator = $translator;

        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    /**
     * @param Subscription $subscription
     */
    public function sendInvitation(Subscription $subscription)
    {
        $contact = $subscription->getContact();

        $message = \Swift_Message::newInstance()
            ->setSubject(
                $this->translator->trans(
                    'mail.invitation.subject',
                    array(
                        '%ticketNo%' => $subscription->getTicketNo()
                    ),
                    null,
                    $subscription->getLocale()
                )
            )
            ->setFrom($this->sender)
            ->setTo(array($contact->getEmail() => $contact->getFirstName() . ' ' . $contact->getLastName()))
            ->setBody(
                $this->renderView(
                    'invitation.' . $subscription->getLocale() . '.html.twig',
                    array('subscription' => $subscription)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription $subscription
     */
    public function sendCreatedNotification(Subscription $subscription)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject(
                $this->translator->trans(
                    'mail.creation.subject',
                    array(
                        '%ticketNo%' => $subscription->getTicketNo()
                    )
                )
            )
            ->setFrom($this->sender)
            ->setTo($this->receiver)
            ->setBody(
                $this->renderView(
                    'admin/mail/creation.html.twig',
                    array('subscription' => $subscription)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription $subscription
     */
    public function sendPublishedNotification(Subscription $subscription)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject(
                $this->translator->trans(
                    'mail.notification.published.subject',
                    array(
                        '%ticketNo%' => $subscription->getTicketNo(),
                        '%nameEn%'   => $subscription->getNameEn(),
                        '%nameNl%'   => $subscription->getNameNl(),
                    )
                )
            )
            ->setFrom($this->sender)
            ->setTo($this->receiver)
            ->setBody(
                $this->renderView(
                    'admin/mail/notification.published.html.twig',
                    array('subscription' => $subscription)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription $subscription
     */
    public function sendPublishedConfirmation(Subscription $subscription)
    {
        $contact = $subscription->getContact();

        $message = \Swift_Message::newInstance()
            ->setSubject(
                $this->translator->trans(
                    'mail.confirmation.published.subject',
                    array(
                        '%ticketNo%' => $subscription->getTicketNo()
                    ),
                    null,
                    $subscription->getLocale()
                )
            )
            ->setFrom($this->sender)
            ->setTo(array($contact->getEmail() => $contact->getFirstName() . ' ' . $contact->getLastName()))
            ->setBody(
                $this->renderView(
                    'confirmation.published.' . $subscription->getLocale() . '.html.twig',
                    array('subscription' => $subscription)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription $subscription
     */
    public function sendFinishedNotification(Subscription $subscription)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject(
                $this->translator->trans(
                    'mail.notification.finished.subject',
                    array(
                        '%ticketNo%' => $subscription->getTicketNo(),
                        '%nameEn%'   => $subscription->getNameEn(),
                        '%nameNl%'   => $subscription->getNameNl(),
                    )
                )
            )
            ->setFrom($this->sender)
            ->setTo($this->receiver)
            ->setBody(
                $this->renderView(
                    'admin/mail/notification.finished.html.twig',
                    array('subscription' => $subscription)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription $subscription
     */
    public function sendFinishedConfirmation(Subscription $subscription)
    {
        $contact = $subscription->getContact();

        $message = \Swift_Message::newInstance()
            ->setSubject(
                $this->translator->trans(
                    'mail.confirmation.finished.subject',
                    array(
                        '%ticketNo%' => $subscription->getTicketNo()
                    ),
                    null,
                    $subscription->getLocale()
                )
            )
            ->setFrom($this->sender)
            ->setTo(array($contact->getEmail() => $contact->getFirstName() . ' ' . $contact->getLastName()))
            ->setBody(
                $this->renderView(
                    'confirmation.finished.' . $subscription->getLocale() . '.html.twig',
                    array('subscription' => $subscription)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription[] $subscriptions
     */
    public function sendReport(array $subscriptions)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans('mail.report.subject'))
            ->setFrom($this->sender)
            ->setTo($this->receiver)
            ->setBody(
                $this->renderView(
                    'admin/mail/report.html.twig',
                    array('subscriptions' => $subscriptions)
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription $subscription
     * @param \Exception   $exception
     */
    public function sendErrorNotification(Subscription $subscription, $xml, \Exception $exception)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans('mail.error.subject'))
            ->setFrom($this->sender)
            ->setTo($this->receiver)
            ->setBody(
                $this->renderView(
                    'admin/mail/error.html.twig',
                    array(
                        'subscription' => $subscription,
                        'xml'          => $xml,
                        'exception'    => $exception,
                    )
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * @param string $view
     * @param array  $parameters
     *
     * @return string
     */
    private function renderView($view, array $parameters = array())
    {
        return $this->templating->render($view, $parameters);
    }
}
