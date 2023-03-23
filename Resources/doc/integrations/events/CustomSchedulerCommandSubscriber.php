<?php

namespace App\EventSubscriber;

use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandCreatedEvent;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandPostExecutionEvent;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandFailedEvent;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandPreExecutionEvent;
use Flashmer\CommandSchedulerBundle\EventSubscriber\SchedulerCommandSubscriber;

/**
 * Example to Subscribe to Events from the Flashmer\CommandSchedulerBundle
 */
class CustomSchedulerCommandSubscriber extends SchedulerCommandSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SchedulerCommandCreatedEvent::class => ['onScheduledCommandCreated',    -10],
            SchedulerCommandFailedEvent::class => ['onScheduledCommandFailed',     20],
            SchedulerCommandPreExecutionEvent::class => ['onScheduledCommandPreExecution',   10],
            SchedulerCommandPostExecutionEvent::class => ['onScheduledCommandPostExecution',   30],
        ];
    }

    public function onScheduledCommandCreated(SchedulerCommandCreatedEvent $event): void
    {
        $this->logger->info('CustomScheduledCommandCreated', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandFailed(SchedulerCommandFailedEvent $event): void
    {
        $this->logger->warning('CustomSchedulerCommandFailedEvent', ['details' => $event->getMessage()]);
    }

    public function onScheduledCommandPreExecution(SchedulerCommandPreExecutionEvent $event): void
    {
        $this->logger->info('CustomScheduledCommandPreExecution', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandPostExecution(SchedulerCommandPostExecutionEvent $event): void
    {
        $this->logger->info('CustomScheduledCommandPostExecution', [
            'name' => $event->getCommand()->getName(),
            "result" => $event->getResult(),
            "runtime" => $event->getRuntime()->format('%S seconds'),
        ]);
    }
}
