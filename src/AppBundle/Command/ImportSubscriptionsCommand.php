<?php

namespace AppBundle\Command;

use AppBundle\Entity\Subscription;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class ImportSubscriptionsCommand
 * @package AppBundle\Command
 */
class ImportSubscriptionsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:subscriptions:import')
            ->setDescription('Import subscriptions from a different environment')
            ->addArgument(
                'dsn',
                InputArgument::REQUIRED,
                'Data Source Name in URL form: <driver>://<username>:<password>@<host>:<port>/<database>'
            )
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'Major version of SP registration database (supported: "1", "2" and "3")'
            )
            ->addOption(
                'environment',
                'env',
                InputOption::VALUE_REQUIRED,
                'Environment to put legacy subscriptions on'
            )
            ->addOption(
                'ask-password',
                'p',
                InputOption::VALUE_NONE,
                "Ask for password (recommended, password won't end up in shell history)"
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = (int) $input->getArgument('version');
        if (!in_array($version, array(1,2,3))) {
            $output->writeln('<error>Unsupported version given.</error>');
            return 1;
        }

        $environment = null;
        if ($version < 3) {
            $environment = $input->getOption('environment');
            $supportedEnvironments = array(
                Subscription::ENVIRONMENT_CONNECT,
                Subscription::ENVIRONMENT_PRODUCTION
            );

            if (!in_array($environment, $supportedEnvironments)) {
                $output->writeln('<error>Invalid or missing required environment.</error>');
                return 1;
            }
        }

        $dsnUrl = $input->getArgument('dsn');
        $parsedDsnUrl = parse_url($dsnUrl);

        if ($input->getOption('ask-password') && $input->isInteractive()) {
            $helper = $this->getHelper('question');
            $question = new Question('Password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);

            $parsedDsnUrl['pass'] = $helper->ask($input, $output, $question);
        }

        $pdo = new PDO(
            $this->buildDsnFromParsedUrl($parsedDsnUrl),
            isset($parsedDsnUrl['user']) ? $parsedDsnUrl['user'] : null,
            isset($parsedDsnUrl['pass']) ? $parsedDsnUrl['pass'] : null
        );

        if ($pdo->query("SELECT 1")->fetchColumn() !== '1') {
            $output->writeln('<error>Unable to connect to the database.</error>');
            return 1;
        }
        $output->writeln('<info>Successfully connected to the database.</info>');

        $queryResult = $pdo->query('SELECT * FROM Subscription');
        if (!$queryResult) {
            $output->writeln('<error>Failure querying Subscription table</error>');
            $output->writeln('<error>Error code: ' . $pdo->errorCode() . '</error>');
            $output->writeln('<error>Error info: ' . print_r($pdo->errorInfo(), true) . '</error>');
            return 1;
        }

        /** @var Connection $database */
        $database = $this->getContainer()->get('doctrine.dbal.default_connection');

        $updateClauses = array();
        foreach ($this->subscriptionFields as $field) {
            if ($field === 'id') {
                continue;
            }
            if ($field === 'updated') {
                $updateClauses[] = "updated = NOW()";
                continue;
            }

            $updateClauses[] = "$field = VALUES($field)";
        }

        $insertQuery = $database->prepare(
            'INSERT INTO Subscription ('
            . implode(', ', $this->subscriptionFields)
            . ') VALUES (?' . str_repeat(', ?', count($this->subscriptionFields) - 1) . ') ON DUPLICATE KEY UPDATE '
            . implode(', ', $updateClauses)
        );

        $output->writeln("Starting import of subscriptions from v$version instance database at '$dsnUrl'.");
        while ($row = $queryResult->fetch(PDO::FETCH_ASSOC)) {
            $row = $this->setRowEnvironment($row, $version, $environment);
            $row = $this->setRowStatus($row, $version);

            $newRow = array();
            foreach ($this->subscriptionFields as $fieldName) {
                if (!array_key_exists($fieldName, $row)) {
                    $output->writeln("{$row['id']} Setting $fieldName to NULL", OutputInterface::VERBOSITY_VERBOSE);
                    $row[$fieldName] = null;
                }
                $newRow[] = $row[$fieldName];
            }

            $insertQuery->execute($newRow);
            $output->writeln("<info>{$row['id']} imported</info>");
        }

        $output->writeln("Import complete");
        return 0;
    }

    /**
     * @param array $parsedDsnUrl
     * @return string
     */
    private function buildDsnFromParsedUrl(array $parsedDsnUrl)
    {
        return $parsedDsnUrl['scheme'] . ':'
            . 'host=' . $parsedDsnUrl['host'] . ';'
            . 'dbname=' . substr($parsedDsnUrl['path'], 1)
            . (isset($parsedDsnUrl['port']) ? 'port=' . $parsedDsnUrl['port'] . ';': '');
    }

    /**
     * @param array $row
     * @param int $version
     * @param string $environment
     */
    private function setRowEnvironment(array $row, $version, $environment)
    {
        if ($version >= 3) {
            return $row;
        }

        $row['environment'] = $environment;
        return $row;
    }

    /**
     * @param array $row
     * @param int $version
     */
    private function setRowStatus(array $row, $version)
    {
        if ($version >= 2) {
            return $row;
        }

        if ($row['status'] === '1') {
            $row['status'] = Subscription::STATE_FINISHED;
        }

        return $row;
    }

    private $subscriptionFields = array(
        'id',
        'locale',
        'archived',
        'status',
        'created',
        'updated',
        'ticketNo',
        'janusId',
        'contact',
        'metadataUrl',
        'acsLocation',
        'entityId',
        'certificate',
        'logoUrl',
        'nameNl',
        'nameEn',
        'descriptionNl',
        'descriptionEn',
        'applicationUrl',
        'eulaUrl',
        'administrativeContact',
        'technicalContact',
        'supportContact',
        'givenNameAttribute',
        'surNameAttribute',
        'commonNameAttribute',
        'displayNameAttribute',
        'emailAddressAttribute',
        'organizationAttribute',
        'organizationTypeAttribute',
        'affiliationAttribute',
        'entitlementAttribute',
        'principleNameAttribute',
        'uidAttribute',
        'preferredLanguageAttribute',
        'personalCodeAttribute',
        'comments',
        'environment',
    );
}
