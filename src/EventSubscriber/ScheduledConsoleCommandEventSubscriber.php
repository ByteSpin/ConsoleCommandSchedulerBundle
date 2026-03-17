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

namespace ByteSpin\ConsoleCommandSchedulerBundle\EventSubscriber;

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\SchedulerLog;
use ByteSpin\ConsoleCommandSchedulerBundle\Event\ScheduledConsoleCommandEvent;
use ByteSpin\ConsoleCommandSchedulerBundle\Processor\NotificationProcessor;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

readonly class ScheduledConsoleCommandEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private NotificationProcessor $notificationProcessor,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScheduledConsoleCommandEvent::LOG => [
                ['logScheduledConsoleCommand'],
            ],
            ScheduledConsoleCommandEvent::AFTER => [
                ['notifyScheduledConsoleCommand'],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function logScheduledConsoleCommand(ScheduledConsoleCommandEvent $event): void
    {
        $consoleCommand = $event->data;
        $logData = new SchedulerLog();
        $logData->setCommand($consoleCommand->command);
        $logData->setArguments(implode(' ', $consoleCommand->commandArguments));
        $logData->setDate($consoleCommand->start);
        $logData->setDuration($consoleCommand->duration);
        $logData->setReturnCode($consoleCommand->returnCode);

        try {
            $entityManager = $this->managerRegistry->getManagerForClass(SchedulerLog::class);
            $entityManager->persist($logData);
            $entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error while logging Scheduled Console Command. Error was: '.$e->getMessage());
        }
    }

    /**
     * @throws \Exception|TransportExceptionInterface|InvalidArgumentException
     */
    public function notifyScheduledConsoleCommand(ScheduledConsoleCommandEvent $event): void
    {
        $consoleCommand = $event->data;

        try {
            $this->notificationProcessor->sendNotification($consoleCommand);
        } catch (\Exception $e) {
            throw new \Exception('Error while sending notification. Error was: '.$e->getMessage());
        }
    }
}
