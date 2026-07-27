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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Exception;

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;

/**
 * A scheduler entry that cannot be converted into a RecurringMessage.
 * Carries a short, human-readable reason (without the entry prefix) so
 * validators and admin screens can surface it as-is.
 */
final class InvalidSchedulerEntryException extends \RuntimeException
{
    private function __construct(
        Scheduler $entry,
        private readonly string $reason,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Scheduler entry #%s (%s): %s',
            $entry->getId() ?? 'new',
            $entry->getJobTitle() ?: ($entry->getCommand() ?? '?'),
            $reason,
        ), 0, $previous);
    }

    public static function missingRequiredFields(Scheduler $entry): self
    {
        return new self($entry, 'command, execution type and frequency are required.');
    }

    public static function unknownExecutionType(Scheduler $entry, ?string $type): self
    {
        return new self($entry, sprintf('unknown execution type "%s" — expected "every" or "cron".', $type ?? ''));
    }

    public static function unknownCommand(Scheduler $entry, string $command, \Throwable $previous): self
    {
        return new self($entry, sprintf('command "%s" does not exist in this application.', $command), $previous);
    }

    public static function invalidFrequency(Scheduler $entry, string $type, string $frequency, \Throwable $previous): self
    {
        $reason = sprintf(
            'frequency "%s" is not a valid "%s" expression (%s).',
            $frequency,
            $type,
            $previous->getMessage(),
        );

        // The classic trap: a cron string typed into the interval field kills
        // the PeriodicalTrigger — point straight at the fix.
        if ('every' === $type && 1 === preg_match('/^\S+(\s+\S+){4,5}$/', trim($frequency))) {
            $reason .= ' The value looks like a cron expression — set the execution type to "cron".';
        }

        return new self($entry, $reason, $previous);
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
