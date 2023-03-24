<?php

namespace Flashmer\CommandSchedulerBundle\Event;

use Flashmer\CommandSchedulerBundle\Entity\ScheduledCommand;

abstract class AbstractSchedulerCommandEvent
{
    public function __construct(private readonly ScheduledCommand $command)
    {
    }

    public function getCommand(): ScheduledCommand
    {
        return $this->command;
    }
}
