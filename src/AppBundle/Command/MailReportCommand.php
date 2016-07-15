<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MailReportCommand
 */
class MailReportCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('app:mail:report')
            ->setDescription('Mail a report of draft registrations');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $subscriptions = $container->get('subscription.repository.doctrine')
            ->findDraftSubscriptions();

        $this->getContainer()->get('mail.manager')->sendReport($subscriptions);

        $output->writeln('Done, sent ' . count($subscriptions) . ' registrations.');
    }
}
