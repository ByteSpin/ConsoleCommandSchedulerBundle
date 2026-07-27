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

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Exception\InvalidSchedulerEntryException;
use ByteSpin\ConsoleCommandSchedulerBundle\Factory\RecurringMessageFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ValidSchedulerEntryValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RecurringMessageFactory $recurringMessageFactory,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidSchedulerEntry) {
            throw new UnexpectedTypeException($constraint, ValidSchedulerEntry::class);
        }
        if (null === $value) {
            return;
        }
        if (!$value instanceof Scheduler) {
            throw new UnexpectedValueException($value, Scheduler::class);
        }

        try {
            $this->recurringMessageFactory->create($value);
        } catch (InvalidSchedulerEntryException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ reason }}', ucfirst($e->getReason()))
                ->atPath('frequency')
                ->addViolation()
            ;
        }
    }
}
