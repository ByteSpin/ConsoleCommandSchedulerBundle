<?php

/**
 * This file is part of the ByteSpin/ConsoleCommandSchedulerBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ByteSpin\ConsoleCommandSchedulerBundle\Event;

use DateTime;
use Symfony\Contracts\EventDispatcher\Event;

final class ScheduledConsoleCommandOutputEvent extends Event
{
    public function __construct(
        public readonly string $commandId,
        public readonly DateTime $dateTime,
        public readonly string $command,
        public readonly ?string $commandArguments,
        public readonly ?string $duration,
        public readonly ?string $returnCode,
        public readonly string $commandOutput,
    ) {
    }
}
