<?php

namespace Flashmer\CommandSchedulerBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandCreatedEvent;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandPostExecutionEvent;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandFailedEvent;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandPreExecutionEvent;
use Flashmer\CommandSchedulerBundle\Notification\CronMonitorNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class SchedulerCommandSubscriber implements EventSubscriberInterface
{
    /**
     * TODO check if parameters needed
     */
    public function __construct(protected LoggerInterface        $logger,
                                protected EntityManagerInterface $em,
                                protected NotifierInterface|null $notifier = null,
                                private readonly array           $monitor_mail = [],
                                private readonly string          $monitor_mail_subject = 'CronMonitor:')
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SchedulerCommandCreatedEvent::class         => ['onScheduledCommandCreated',        -10],
            SchedulerCommandFailedEvent::class          => ['onScheduledCommandFailed',         20],
            SchedulerCommandPreExecutionEvent::class    => ['onScheduledCommandPreExecution',   10],
            SchedulerCommandPostExecutionEvent::class   => ['onScheduledCommandPostExecution',  30],
        ];
    }

    // TODO check if useful (could be handled by doctrine lifecycle events)
    public function onScheduledCommandCreated(SchedulerCommandCreatedEvent $event): void
    {
        $this->logger->info('ScheduledCommandCreated', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandFailed(SchedulerCommandFailedEvent $event): void
    {
        # notifier is optional
        if($this->notifier)
        {
            //...$this->notifier->getAdminRecipients()
            $recipients = [];
            foreach ($this->monitor_mail as $mailadress) {
                $recipients[] = new Recipient($mailadress);
            }

            $this->notifier->send(new CronMonitorNotification($event->getFailedCommands(), $this->monitor_mail_subject), ...$recipients);
        }

        //$this->logger->warning('SchedulerCommandFailedEvent', ['details' => $event->getMessage()]);
    }

    public function onScheduledCommandPreExecution(SchedulerCommandPreExecutionEvent $event): void
    {
        #var_dump('ScheduledCommandPreExecution');
        $this->logger->info('ScheduledCommandPreExecution', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandPostExecution(SchedulerCommandPostExecutionEvent $event): void
    {
        #var_dump('ScheduledCommandPostExecution');

        $this->logger->info('ScheduledCommandPostExecution', [
            'name' => $event->getCommand()->getName(),
            "result" => $event->getResult(),
            #"log" => $event->getLog(),
            "runtime" => $event->getRuntime()->format('%S seconds'),
            #"exception" => $event->getException()?->getMessage() ?? null
        ]);
    }
}
