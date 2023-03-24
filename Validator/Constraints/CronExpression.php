<?php

namespace Flashmer\CommandSchedulerBundle\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[Attribute] class CronExpression extends Constraint
{
    /**
     * Constraint error message.
     */
    public string $message = 'The string "{{ string }}" is not a valid cron expression.';
}
