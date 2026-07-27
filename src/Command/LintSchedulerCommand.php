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

use ByteSpin\ConsoleCommandSchedulerBundle\Factory\RecurringMessageFactory;
use ByteSpin\ConsoleCommandSchedulerBundle\Repository\SchedulerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Dry-runs every scheduler entry through the same factory the schedule
 * provider uses: what lints green is what runs. Meant for CI, deploy
 * pipelines and post-restore sanity checks; a faulty DISABLED entry is
 * reported but does not fail the command.
 */
#[AsCommand(
    name: 'bytespin:scheduler:lint',
    description: 'Validate every scheduled console command entry',
)]
final class LintSchedulerCommand extends Command
{
    public function __construct(
        private readonly SchedulerRepository $schedulerRepository,
        private readonly RecurringMessageFactory $recurringMessageFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rows = [];
        $enabledFaults = 0;

        foreach ($this->schedulerRepository->findAll() as $entry) {
            $state = ($entry->getDisabled()) ? 'disabled' : 'enabled';
            try {
                $this->recurringMessageFactory->create($entry);
                $rows[] = [$entry->getId(), $entry->getJobTitle() ?: $entry->getCommand(), $state, '<info>OK</info>'];
            } catch (\Throwable $e) {
                $rows[] = [
                    $entry->getId(),
                    $entry->getJobTitle() ?: $entry->getCommand(),
                    $state,
                    '<error>' . $e->getMessage() . '</error>',
                ];
                if (!$entry->getDisabled()) {
                    ++$enabledFaults;
                }
            }
        }

        if ([] === $rows) {
            $io->success('No scheduler entry to lint.');

            return Command::SUCCESS;
        }

        $io->table(['ID', 'Job', 'State', 'Result'], $rows);

        if ($enabledFaults > 0) {
            $io->error(sprintf('%d enabled entry(ies) cannot run — fix them or disable them.', $enabledFaults));

            return Command::FAILURE;
        }

        $io->success('Every enabled scheduler entry builds a valid recurring message.');

        return Command::SUCCESS;
    }
}
