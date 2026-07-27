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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Tests\Validator;

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Factory\CommandIntrospectorInterface;
use ByteSpin\ConsoleCommandSchedulerBundle\Factory\RecurringMessageFactory;
use ByteSpin\ConsoleCommandSchedulerBundle\Validator\ValidSchedulerEntry;
use ByteSpin\ConsoleCommandSchedulerBundle\Validator\ValidSchedulerEntryValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<ValidSchedulerEntryValidator>
 */
final class ValidSchedulerEntryValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ValidSchedulerEntryValidator
    {
        $introspector = new class implements CommandIntrospectorInterface {
            public function hasJobIdOption(string $command): bool
            {
                return false;
            }
        };

        return new ValidSchedulerEntryValidator(new RecurringMessageFactory($introspector));
    }

    public function testValidCronEntryPasses(): void
    {
        $this->validator->validate($this->entry('cron', '*/5 * * * 1-5'), new ValidSchedulerEntry());

        $this->assertNoViolation();
    }

    public function testValidEveryEntryPasses(): void
    {
        $this->validator->validate($this->entry('every', '10 seconds'), new ValidSchedulerEntry());

        $this->assertNoViolation();
    }

    public function testCronExpressionInIntervalFieldIsRefusedAtFrequencyPath(): void
    {
        $constraint = new ValidSchedulerEntry();
        $this->validator->validate($this->entry('every', '0 0 * * 1-5'), $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertSame('property.path.frequency', $violations[0]->getPropertyPath());
        $reason = (string) $violations[0]->getParameters()['{{ reason }}'];
        $this->assertStringContainsString('looks like a cron expression', $reason);
    }

    private function entry(string $executionType, string $frequency): Scheduler
    {
        $entry = new Scheduler();
        $entry->setCommand('app:demo');
        $entry->setExecutionType($executionType);
        $entry->setFrequency($frequency);

        return $entry;
    }
}
