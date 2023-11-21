<?php

/**
 * Copyright (c) 2023 Greg LAMY <greg@bytespin.net>
 *
 * This project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git
 *
 * This bundle was originally developed as part of an ETL project.
 *
 * ByteSpin/ConsoleCommandSchedulerBundle is a Symfony 6.3 simple bundle that allows you to schedule console commands easily:
 * - Use the latest messenger/scheduler Symfony 6.3+ components,
 * - Log all console commands data (last execution time, duration, return code) in database and log file,
 * - An admin interface is available with the help of EasyCorp/EasyAdmin bundle
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\EventSubscriber;

use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\SchedulerLog;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\LogConsoleCommand;
use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ScheduledConsoleCommandEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'log.scheduled.console.command' => [
                ['logScheduledConsoleCommand'],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function logScheduledConsoleCommand(GenericEvent $event): void
    {
        /** @var LogConsoleCommand $consoleCommand */
        $consoleCommand = $event->getSubject();
        $logData = new SchedulerLog();
        $logData->setCommand($consoleCommand->command);
        $logData->setArguments(implode(' ', $consoleCommand->commandArguments));
        $logData->setDate($consoleCommand->start);
        $logData->setDuration($consoleCommand->duration);
        $logData->setReturnCode($consoleCommand->return_code);

        try {
            $this->entityManager->persist($logData);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new Exception('Error while logging Scheduled Console Command. Error was: ' . $e->getMessage());
        }
    }
}
