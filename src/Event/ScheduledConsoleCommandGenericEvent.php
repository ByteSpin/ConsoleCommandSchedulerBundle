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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Event;

final readonly class ScheduledConsoleCommandGenericEvent
{
    /**
     * @param array<string> $commandArguments
     */
    public function __construct(
        public string $command,
        public array $commandArguments = [],
        public ?\DateTimeInterface $start = new \DateTime(),
        public ?\DateTimeInterface $end = new \DateTime(),
        public string $duration = '',
        public ?int $returnCode = null,
        public ?string $logFile = null,
        public int $id = 0,
        public ?bool $noDbLog = false,
    ) {
    }
}
