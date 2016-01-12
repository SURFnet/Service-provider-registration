<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Subscription;
use Swift_Message;
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
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $receiver;

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

        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.invitation.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo()
                ),
                null,
                $subscription->getLocale()
            )
        );
        $message->setTo(
            array(
                $contact->getEmail() => $contact->getFirstName()
                                        . ' '
                                        . $contact->getLastName()
            )
        );
        $message->setBody(
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
        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.creation.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo()
                )
            )
        );
        $message->setBody(
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
        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.notification.published.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo(),
                    '%nameEn%'   => $subscription->getNameEn(),
                    '%nameNl%'   => $subscription->getNameNl(),
                )
            )
        );
        $message->setBody(
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

        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.confirmation.published.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo()
                ),
                null,
                $subscription->getLocale()
            )
        );
        $message->setTo(
            array(
                $contact->getEmail() => $contact->getFirstName()
                                        . ' '
                                        . $contact->getLastName()
            )
        );
        $message->setBody(
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
    public function sendUpdatedNotification(Subscription $subscription)
    {
        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.notification.updated.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo(),
                    '%nameEn%'   => $subscription->getNameEn(),
                    '%nameNl%'   => $subscription->getNameNl(),
                )
            )
        );
        $message->setBody(
            $this->renderView(
                'admin/mail/notification.updated.html.twig',
                array('subscription' => $subscription)
            ),
            'text/html'
        );

        $this->mailer->send($message);
    }

    /**
     * @param Subscription $subscription
     */
    public function sendUpdatedConfirmation(Subscription $subscription)
    {
        $contact = $subscription->getContact();

        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.confirmation.updated.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo()
                ),
                null,
                $subscription->getLocale()
            )
        );
        $message->setTo(
            array(
                $contact->getEmail() => $contact->getFirstName()
                    . ' '
                    . $contact->getLastName()
            )
        );
        $message->setBody(
            $this->renderView(
                'confirmation.updated.' . $subscription->getLocale() . '.html.twig',
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
        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.notification.finished.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo(),
                    '%nameEn%'   => $subscription->getNameEn(),
                    '%nameNl%'   => $subscription->getNameNl(),
                )
            )
        );
        $message->setBody(
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

        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.confirmation.finished.subject',
                array(
                    '%ticketNo%' => $subscription->getTicketNo()
                ),
                null,
                $subscription->getLocale()
            )
        );
        $message->setTo(array($contact->getEmail() => $contact->getFirstName() . ' ' . $contact->getLastName()));
        $message->setBody(
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
        $message = $this->createNewMessage();
        $message->setSubject($this->translator->trans('mail.report.subject'));
        $message->setBody(
            $this->renderView(
                'admin/mail/report.html.twig',
                array('subscriptions' => $subscriptions)
            ),
            'text/html'
        );

        $this->mailer->send($message);
    }

    /**
     * @return Swift_Message
     */
    private function createNewMessage()
    {
        $message = Swift_Message::newInstance();

        $headers = $message->getHeaders();
        $headers->addTextHeader('Auto-Submitted', 'auto-generated');
        # TODO: ugly hack, see https://github.com/swiftmailer/swiftmailer/issues/705
        $message->setReturnPath('no-reply@surfconext.nl');
        $message->setFrom($this->sender);
        $message->setTo($this->receiver);

        return $message;
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
