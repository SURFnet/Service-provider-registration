<?php

namespace AppBundle\Command;

use AppBundle\Entity\Template;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SyncTemplatesCommand
 */
class SyncTemplatesCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('app:templates:sync')->setDescription('Sync templates from FS to DB');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Start syncing templates...'));

        $this->sync('invitation');
        $this->sync('confirmation.published');
        $this->sync('confirmation.finished');

        $output->writeln(sprintf('Finished!'));
    }

    /**
     * @param string $prefix
     */
    private function sync($prefix)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $manager = $doctrine->getManager();
        $repository = $doctrine->getRepository('AppBundle:Template');

        $name = $prefix . '.nl.html.twig';
        $template = $repository->findBy(array('name' => $name));

        if (empty($template)) {
            $template = new Template();
            $template->setName($name);
            $template->setSource(
                <<<'EOT'
Beste {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Bla bla</p>

<p>
    <b><a href="{{ url('form', {id: subscription.id} ) }}">Link naar formulier</a></b>
</p>

Met vriendelijke groet,<br>
<br>
SURFconext
EOT
            );
            $manager->persist($template);
        }

        $name = $prefix . '.en.html.twig';
        $template = $repository->findBy(array('name' => $name));

        if (empty($template)) {
            $template = new Template();
            $template->setName($name);
            $template->setSource(
                <<<'EOT'
Dear {{ subscription.contact.firstName}} {{ subscription.contact.lastName }},<br>

<p>Bla bla</p>

<p>
    <b><a href="{{ url('form', {id: subscription.id} ) }}">Link to form</a></b>
</p>

Kind regards,<br>
<br>
SURFconext
EOT
            );
            $manager->persist($template);
        }

        $manager->flush();
    }
}
