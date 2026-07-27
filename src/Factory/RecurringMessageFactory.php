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

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Exception\InvalidSchedulerEntryException;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\ExecuteConsoleCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Scheduler\RecurringMessage;

/**
 * Single point of truth converting a Scheduler entry into a RecurringMessage.
 * Every consumer of an entry's frequency goes through here — the schedule
 * provider at runtime, the entity validator at input time and the lint
 * command — so what validates is exactly what runs.
 *
 * @throws InvalidSchedulerEntryException on any entry that cannot run
 */
final readonly class RecurringMessageFactory
{
    public function __construct(
        private CommandIntrospectorInterface $commandIntrospector,
    ) {
    }

    public function create(Scheduler $entry): RecurringMessage
    {
        $command = $entry->getCommand();
        $frequency = $entry->getFrequency();
        $type = $entry->getExecutionType();
        if (null === $command || '' === $command || null === $frequency || '' === $frequency) {
            throw InvalidSchedulerEntryException::missingRequiredFields($entry);
        }

        $arguments = ($entry->getArguments())
            ? explode(' ', $entry->getArguments())
            : []
        ;

        // add job id to arguments for optional use in run commands
        try {
            if ($this->commandIntrospector->hasJobIdOption($command)) {
                $arguments[] = '--job-id=' . ($entry->getId() ?? 0);
            }
        } catch (CommandNotFoundException $e) {
            throw InvalidSchedulerEntryException::unknownCommand($entry, $command, $e);
        }

        $executeCommand = new ExecuteConsoleCommand(
            $command,
            $arguments,
            $entry->getLogFile(),
            $entry->getId() ?? 0,
            $entry->getNoDbLog(),
        );
        $queue = $entry->getMessengerQueue();
        $message = (null === $queue || '' === $queue)
            ? $executeCommand
            : new RedispatchMessage($executeCommand, $queue)
        ;

        try {
            return match ($type) {
                'cron' => RecurringMessage::cron($frequency, $message),
                'every' => RecurringMessage::every($frequency, $message, $this->from($entry), $this->until($entry)),
                default => throw InvalidSchedulerEntryException::unknownExecutionType($entry, $type),
            };
        } catch (InvalidSchedulerEntryException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw InvalidSchedulerEntryException::invalidFrequency($entry, (string) $type, $frequency, $e);
        }
    }

    private function from(Scheduler $entry): \DateTimeImmutable
    {
        $date = $this->asString($entry->getExecutionFromDate(), 'Y-m-d');
        $time = $this->asString($entry->getExecutionFromTime(), 'H:i:s');

        return new \DateTimeImmutable($date . ' ' . $time, new \DateTimeZone('Europe/Paris'));
    }

    private function until(Scheduler $entry): \DateTimeImmutable
    {
        $date = $this->asString($entry->getExecutionUntilDate(), 'Y-m-d');
        $time = $this->asString($entry->getExecutionUntilTime(), 'H:i:s');

        return ('' === $date && '' === $time)
            ? new \DateTimeImmutable('3000-01-01')
            : new \DateTimeImmutable($date . ' ' . $time, new \DateTimeZone('Europe/Paris'))
        ;
    }

    private function asString(\DateTimeInterface|string|null $value, string $format): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        return $value ?? '';
    }
}
