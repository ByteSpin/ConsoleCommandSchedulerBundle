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
use DateTimeInterface;

final readonly class ScheduledConsoleCommandLogEvent
{
    public function __construct(
        public string $command,
        public array $commandArguments = [],
        public DateTimeInterface|null $start = new DateTime(),
        public string $duration = '',
        public int|null $return_code = null,
        public string|null $logFile = null,
    ) {
    }
}
