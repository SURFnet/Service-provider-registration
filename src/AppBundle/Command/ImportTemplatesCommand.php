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
class ImportTemplatesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('app:templates:import')
            ->setDescription('Import templates from FS to DB')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to import from',
                './app/Resources/views/subscription/mail'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting template import');

        $path = $input->getArgument('path');
        $files = glob($path . '/*.twig');

        if (empty($files)) {
            $output->writeln('<comment>No files found to import</comment>');
            return 0;
        }

        $doctrine = $this->getContainer()->get('doctrine');
        $manager = $doctrine->getManager();
        $repository = $doctrine->getRepository('AppBundle:Template');

        $templates = $repository->findAll();
        foreach ($templates as $template) {
            $manager->remove($template);
            $output->writeln("<info>Removed " . $template->getName() . "<info>");
        }

        foreach ($files as $file) {
            $name = basename($file);

            $output->writeln("<info>Creating $name<info>");
            $template = new Template();
            $template->setName($name);

            $template->setSource(file_get_contents($file));
            $template->setModified(new \DateTime());

            $manager->persist($template);
        }
        $manager->flush();

        $output->writeln('Finish template import');
    }
}
