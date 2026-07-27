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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class-level constraint on the Scheduler entity: the frequency must build
 * a real RecurringMessage for the chosen execution type. Validation runs the
 * SAME factory as the schedule provider, so an entry that saves is an entry
 * that runs — the "cron string in the interval field" trap is rejected at
 * input time instead of killing the schedule at runtime.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class ValidSchedulerEntry extends Constraint
{
    public string $message = '{{ reason }}';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
