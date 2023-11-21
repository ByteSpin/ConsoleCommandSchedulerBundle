<?php

/**
 * Copyright (c) 2023 Greg LAMY <greg@bytespin.net>
 *
 * This project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git
 *
 * This bundle was originally developed as part of an ETL project.
 *
 * ByteSpin/ConsoleCommandSchedulerBundle is a Symfony 6.3 simple bundle that allows you to schedule console commands easily:
 * - Use the latest messenger/scheduler Symfony 6.3+ components,
 * - Log all console commands data (last execution time, duration, return code) in database and log file,
 * - An admin interface is available with the help of EasyCorp/EasyAdmin bundle
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\Provider;

use AllowDynamicProperties;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

#[AllowDynamicProperties] class ConsoleCommandProvider
{

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
        $this->application = new Application($this->kernel);
        $this->application->setAutoExit(false);
    }

    public function getConsoleCommands(): string|array
    {
        $allCommands = $this->application->all();
        $commandNames = [];

        foreach ($allCommands as $command) {
            $commandNames[] = $command->getName();
        }
        return $commandNames;
    }

    public function listConsoleCommands(): array
    {
        $commands = $this->getConsoleCommands();
        $listCommand = [];
        foreach ($commands as $key => $val) {
            $listCommand[$val] = $val;
        }
        return $listCommand;
    }
}
