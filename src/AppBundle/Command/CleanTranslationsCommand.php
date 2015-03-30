<?php

namespace AppBundle\Command;

use Lexik\Bundle\TranslationBundle\Model\File;
use Lexik\Bundle\TranslationBundle\Storage\StorageInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CleanTranslationsCommand
 */
class CleanTranslationsCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('app:translations:clean')
            ->setDescription('Clean unused translations from DB');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Start cleaning unused translations...'));

        /** @var StorageInterface $translationStorage */
        $translationStorage = $this->getContainer()->get('lexik_translation.translation_storage');
        $translator = $this->getContainer()->get('lexik_translation.translator');

        /** @var File[] $files */
        $files = $translationStorage->getFilesByLocalesAndDomains(array('en'), array());

        foreach ($files as $file) {
            $output->writeln(sprintf(' > Processing translations from file `<info>%s</info>`', $file->getName()));

            // Read all from DB
            $dbTranslations = $translationStorage->getTranslationsFromFile($file, false);

            // Read all from file
            $filePath = sprintf(
                '%s/%s/%s',
                $this->getContainer()->getParameter('kernel.root_dir'),
                $file->getPath(),
                $file->getName()
            );
            $loader = $translator->getLoader($file->getExtention());
            $messageCatalogue = $loader->load($filePath, $file->getLocale(), $file->getDomain());
            $fileTranslations = $messageCatalogue->all($file->getDomain());

            // Determine difference between db and file
            $diff = array_keys(array_diff_key($dbTranslations, $fileTranslations));

            // Remove remaining from DB
            foreach ($diff as $key) {
                $output->writeln(sprintf('  >> Deleting translations for `<info>%s</info>`', $key));

                $transUnit = $translationStorage->getTransUnitByKeyAndDomain($key, $file->getDomain());
                $this->getContainer()->get('doctrine')->getManager()->remove($transUnit);
            }
            $translationStorage->flush();
        }

        $output->writeln(sprintf('Finished!'));
    }
}
