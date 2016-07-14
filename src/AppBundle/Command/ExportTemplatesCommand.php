<?php

namespace AppBundle\Command;

use AppBundle\Entity\Template;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SyncTemplatesCommand
 */
class ExportTemplatesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('app:templates:export')
            ->setDescription('Export templates from DB to FS')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to export from',
                './app/Resources/views/subscription/mail'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting template export');

        $path = $input->getArgument('path');

        if (!is_writeable($path)) {
            $output->writeln("<error>Path '$path' is not writable</error>");
            return 1;
        }

        $repository = $this->getContainer()->get('doctrine')->getRepository('AppBundle:Template');

        $templates = $repository->findAll();

        foreach ($templates as $template) {
            $filePath = $path . '/' . $template->getName();

            file_put_contents($filePath, $template->getSource());
            touch($filePath, $template->getModified()->getTimestamp());
            $output->writeln("<info>Wrote $filePath</info>");
        }

        $output->writeln('Finish template export');
        return 0;
    }
}
