<?php

/**
 * This file is part of the ByteSpin/ConsoleCommandSchedulerBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\EventSubscriber;

use AllowDynamicProperties;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\SchedulerLog;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\LogConsoleCommand;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ScheduledConsoleCommandEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
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
            $entityManager = $this->managerRegistry->getManagerForClass(SchedulerLog::class);
            $entityManager->persist($logData);
            $entityManager->flush();
        } catch (Exception $e) {
            throw new Exception('Error while logging Scheduled Console Command. Error was: ' . $e->getMessage());
        }
    }
}
