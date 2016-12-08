<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateMetadataXmlCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('app:metadataxml:update')
            ->setDescription('Reimport XML from metadataUrl for all subscriptions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting update of Subscriptions');

        /** @var \AppBundle\Entity\Subscription[] $subscriptions */
        $repository = $this->getContainer()->get('subscription.repository.doctrine');
        $subscriptions = $repository->findAll();

        foreach ($subscriptions as $subscription) {
            $oldSubscription = clone $subscription;
            $changed = false;

            $id = $subscription->getId();
            $importUrl = $subscription->getImportUrl();
            $metadataUrl = $subscription->getMetadataUrl();

            if (empty($metadataUrl)) {
                $output->writeln("<info>[$id] Skipping: Empty metadata url</info>");
                continue;
            }

            if (empty($importUrl) && !empty($metadataUrl)) {
                $subscription->setImportUrl($metadataUrl);
                $output->writeln("<info>[$id] Updating importUrl</info>");
                $changed = true;
            }

            $fetcher = $this->getContainer()->get('fetcher');
            try {
                $xml = $fetcher->fetch($metadataUrl);
            } catch (\InvalidArgumentException $e) {
                $message = $e->getMessage();
                $output->writeln("<error>[$id] Skipping: Error fetching from '$metadataUrl': $message</error>");
                continue;
            }

            if (empty($xml)) {
                $output->writeln("<error>[$id] Skipping: Error fetching from '$metadataUrl': empty response</error>");
                continue;
            }

            if ($subscription->getMetadataXml() !== $xml) {
                $subscription->setMetadataXml($xml);
                $output->writeln("<info>[$id] Updating XML</info>");
                $changed = true;
            }

            if (!$changed) {
                $output->writeln("<info>[$id] Skipping: Nothing changed</info>");
                continue;
            }

            $repository->update($oldSubscription, $subscription);
        }

        return 0;
    }
}
