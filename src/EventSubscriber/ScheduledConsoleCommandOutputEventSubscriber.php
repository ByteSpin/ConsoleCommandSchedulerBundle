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

use ByteSpin\ConsoleCommandSchedulerBundle\Job\JobOutputCollector;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ByteSpin\ConsoleCommandSchedulerBundle\Event\ScheduledConsoleCommandOutputEvent;

class ScheduledConsoleCommandOutputEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public JobOutputCollector $outputCollector,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScheduledConsoleCommandOutputEvent::class => 'onCommandOutput',
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function onCommandOutput(ScheduledConsoleCommandOutputEvent $event): void
    {
        $output = [
            'dateTime' => $event->dateTime,
            'commandType' => $event->commandType,
            'command' => $event->command,
            'commandArguments' => $event->commandArguments,
            'duration' => $event->duration,
            'returnCode' => $event->returnCode,
            'output' => $event->commandOutput
        ]
        ;

        $this->outputCollector->addOutput($event->commandId, $output);
    }
}
