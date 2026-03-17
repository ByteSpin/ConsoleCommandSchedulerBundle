<?php

/**
 * This file is part of the ByteSpin/ConsoleCommandSchedulerBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git.
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ByteSpin\ConsoleCommandSchedulerBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'bytespin:configure-console-command-scheduler',
    description: 'Configure the ByteSpin Console Command Scheduler Bundle',
)]
class ConfigureBundleCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('ByteSpin Console Command Scheduler Bundle Configuration');

        $this->configureDoctrine($io, $input);
        $this->updateBundlesFile($io, $input);

        $io->success('Configuration completed.');

        return Command::SUCCESS;
    }

    private function configureDoctrine(SymfonyStyle $io, InputInterface $input): void
    {
        $doctrineConfigFile = $this->projectDir.'/config/packages/doctrine.yaml';

        if (!file_exists($doctrineConfigFile)) {
            $io->warning('The file config/packages/doctrine.yaml does not exist. Skipping Doctrine configuration.');

            return;
        }

        $config = Yaml::parseFile($doctrineConfigFile);
        $mappingKey = 'ByteSpin\\ConsoleCommandSchedulerBundle';

        // Determine which DBAL connection to use
        if (empty($config['doctrine']['dbal']['connections'])) {
            $selectedConnection = 'default';
            $io->note('No named DBAL connections found in doctrine.yaml. Using the default connection.');
        } else {
            $connections = array_keys($config['doctrine']['dbal']['connections']);

            if ($input->isInteractive()) {
                $selectedConnection = $io->choice('Select a DBAL connection', $connections, $connections[0]);
            } else {
                $selectedConnection = $connections[0];
                $io->note(sprintf('Non-interactive mode: using connection "%s".', $selectedConnection));
            }
        }

        // Check if mapping already exists
        if (isset($config['doctrine']['orm']['entity_managers'][$selectedConnection]['mappings'][$mappingKey])) {
            $io->note(sprintf(
                'The mapping for %s already exists for entity manager "%s". Skipping.',
                $mappingKey,
                $selectedConnection,
            ));

            return;
        }

        $mapping = [
            'is_bundle' => false,
            'type' => 'attribute',
            'dir' => '%kernel.project_dir%/vendor/bytespin/console-command-scheduler-bundle/src/Entity',
            'prefix' => 'ByteSpin\\ConsoleCommandSchedulerBundle\\Entity',
            'alias' => 'ByteSpin\\ConsoleCommandSchedulerBundle',
        ];

        // Create or update entity manager configuration
        if (!isset($config['doctrine']['orm']['entity_managers'][$selectedConnection])) {
            $io->note(sprintf('Creating entity manager for connection: %s', $selectedConnection));
            $config['doctrine']['orm']['entity_managers'][$selectedConnection] = [
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'connection' => $selectedConnection,
                'mappings' => [$mappingKey => $mapping],
            ];
        } else {
            $io->note(sprintf('Adding mapping to entity manager: %s', $selectedConnection));
            $config['doctrine']['orm']['entity_managers'][$selectedConnection]['mappings'][$mappingKey] = $mapping;
        }

        if (!$input->isInteractive() || $io->confirm('Write changes to doctrine.yaml?', true)) {
            file_put_contents(
                $doctrineConfigFile,
                Yaml::dump($config, 10, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK),
            );
            $io->success('doctrine.yaml has been updated.');
        }
    }

    private function updateBundlesFile(SymfonyStyle $io, InputInterface $input): void
    {
        $bundlesFilePath = $this->projectDir.'/config/bundles.php';

        if (!file_exists($bundlesFilePath)) {
            $io->warning('The file config/bundles.php does not exist. Skipping.');

            return;
        }

        $bundlesFileContent = file_get_contents($bundlesFilePath);
        $bundleClass = 'ByteSpin\\ConsoleCommandSchedulerBundle\\ConsoleCommandSchedulerBundle';

        if (str_contains($bundlesFileContent, $bundleClass.'::class')) {
            $io->note('Bundle is already registered in bundles.php. Skipping.');

            return;
        }

        if (!$input->isInteractive() || $io->confirm('Register the bundle in bundles.php?', true)) {
            $newBundleLine = "    {$bundleClass}::class => ['all' => true],";
            $bundlesFileContent = str_replace('];', $newBundleLine.PHP_EOL.'];', $bundlesFileContent);
            file_put_contents($bundlesFilePath, $bundlesFileContent);

            $io->success('Bundle has been registered in bundles.php.');
        }
    }
}
