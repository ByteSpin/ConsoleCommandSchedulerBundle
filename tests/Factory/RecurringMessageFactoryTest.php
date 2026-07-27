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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Tests\Factory;

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Exception\InvalidSchedulerEntryException;
use ByteSpin\ConsoleCommandSchedulerBundle\Factory\CommandIntrospectorInterface;
use ByteSpin\ConsoleCommandSchedulerBundle\Factory\RecurringMessageFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Scheduler\RecurringMessage;

final class RecurringMessageFactoryTest extends TestCase
{
    public function testCronEntryBuildsARecurringMessage(): void
    {
        $message = $this->factory()->create($this->entry('cron', '*/5 * * * 1-5'));

        $this->assertInstanceOf(RecurringMessage::class, $message);
    }

    public function testEveryEntryBuildsARecurringMessage(): void
    {
        $message = $this->factory()->create($this->entry('every', '5 minutes'));

        $this->assertInstanceOf(RecurringMessage::class, $message);
    }

    public function testCronExpressionInTheIntervalFieldIsRejectedWithAHint(): void
    {
        $this->expectException(InvalidSchedulerEntryException::class);
        $this->expectExceptionMessageMatches('/looks like a cron expression/');

        $this->factory()->create($this->entry('every', '0 0 * * 1-5'));
    }

    public function testInvalidCronExpressionIsRejected(): void
    {
        $this->expectException(InvalidSchedulerEntryException::class);
        $this->expectExceptionMessageMatches('/not a valid "cron" expression/');

        $this->factory()->create($this->entry('cron', 'definitely not a cron'));
    }

    public function testUnknownExecutionTypeIsRejected(): void
    {
        $this->expectException(InvalidSchedulerEntryException::class);
        $this->expectExceptionMessageMatches('/unknown execution type "sometimes"/');

        $this->factory()->create($this->entry('sometimes', '5 minutes'));
    }

    public function testMissingFrequencyIsRejected(): void
    {
        $this->expectException(InvalidSchedulerEntryException::class);
        $this->expectExceptionMessageMatches('/required/');

        $entry = new Scheduler();
        $entry->setCommand('app:demo');

        $this->factory()->create($entry);
    }

    public function testUnknownCommandIsRejected(): void
    {
        $introspector = new class implements CommandIntrospectorInterface {
            public function hasJobIdOption(string $command): bool
            {
                throw new CommandNotFoundException(sprintf('Command "%s" is not defined.', $command));
            }
        };

        $this->expectException(InvalidSchedulerEntryException::class);
        $this->expectExceptionMessageMatches('/does not exist in this application/');

        (new RecurringMessageFactory($introspector))->create($this->entry('cron', '* * * * *'));
    }

    private function factory(bool $hasJobIdOption = false): RecurringMessageFactory
    {
        $introspector = new class ($hasJobIdOption) implements CommandIntrospectorInterface {
            public function __construct(private readonly bool $hasJobIdOption)
            {
            }

            public function hasJobIdOption(string $command): bool
            {
                return $this->hasJobIdOption;
            }
        };

        return new RecurringMessageFactory($introspector);
    }

    private function entry(string $executionType, string $frequency): Scheduler
    {
        $entry = new Scheduler();
        $entry->setCommand('app:demo');
        $entry->setExecutionType($executionType);
        $entry->setFrequency($frequency);
        $entry->setJobTitle('Demo job');

        return $entry;
    }
}
