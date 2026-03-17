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

use Symfony\Contracts\EventDispatcher\Event;

final class ScheduledConsoleCommandEvent extends Event
{
    public const BEFORE = 'bytespin.before.scheduled.console.command';
    public const LOG = 'bytespin.log.scheduled.console.command';
    public const SUCCESS = 'bytespin.success.scheduled.console.command';
    public const FAILURE = 'bytespin.failure.scheduled.console.command';
    public const AFTER = 'bytespin.after.scheduled.console.command';

    public function __construct(
        public readonly ScheduledConsoleCommandGenericEvent $data,
    ) {
    }
}
