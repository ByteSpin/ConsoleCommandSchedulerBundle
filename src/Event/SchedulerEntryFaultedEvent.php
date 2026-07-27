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

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched when a scheduler entry cannot be turned into a RecurringMessage
 * while building the schedule: the entry is skipped (the rest of the schedule
 * keeps running) and host applications can hook their own monitoring here.
 */
final class SchedulerEntryFaultedEvent extends Event
{
    public const NAME = 'bytespin.scheduler.entry.faulted';

    public function __construct(
        public readonly Scheduler $entry,
        public readonly \Throwable $error,
    ) {
    }
}
