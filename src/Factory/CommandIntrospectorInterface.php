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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Factory;

use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Abstraction over the console application so the RecurringMessageFactory
 * stays unit-testable without booting a kernel.
 */
interface CommandIntrospectorInterface
{
    /**
     * @throws CommandNotFoundException when the command does not exist
     */
    public function hasJobIdOption(string $command): bool;
}
