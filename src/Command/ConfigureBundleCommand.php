<?php

/**
 * This file is part of the ByteSpin/MessengerDedupeBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/MessengerDedupeBundle.git
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ByteSpin\ConsoleCommandSchedulerBundle\Scripts\PostInstallScript;

class ConfigureBundleCommand extends Command
{
    protected static $defaultName = 'bytespin:configure-console-command-scheduler';

    protected function configure(): void
    {
        $this
            ->setDescription('Configure the ByteSpin Console Command Scheduler Bundle.')
            ->setHelp('This command allows you to configure the ByteSpin Console Command Scheduler Bundle ...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        PostInstallScript::postInstall();

        return Command::SUCCESS;
    }
}