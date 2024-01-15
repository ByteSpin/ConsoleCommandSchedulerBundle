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

declare(strict_types=1);

namespace ByteSpin\ConsoleCommandSchedulerBundle\Scheduler;

use AllowDynamicProperties;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\ExecuteConsoleCommand;
use ByteSpin\ConsoleCommandSchedulerBundle\Repository\SchedulerRepository;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;

#[AllowDynamicProperties] #[AsSchedule('scheduler')]
final class ConsoleJobsScheduler implements ScheduleProviderInterface
{
    public function __construct(
        private readonly SchedulerRepository $schedulerRepository,
        private readonly KernelInterface $kernel,
    ) {
        $this->application = new Application($this->kernel);
        $this->application->setAutoExit(false);
    }
    /**
     * @throws Exception
     */
    public function getSchedule(): Schedule
    {
        $commands = $this->schedulerRepository->findBy(['disabled' => false]);
        $scheduler = new Schedule();
        foreach ($commands as $item) {
            $frequency = $item->getFrequency();
            $log_file = $item->getLogFile();
            $command = $item->getCommand();
            $id = $item->getId();
            $no_db_log = $item->getNoDbLog();
            $arguments = ($item->getArguments())
                ? explode(' ', $item->getArguments())
                : []
            ;

            // add job id to arguments for optional use in run commands
            if ($this->hasJobIdOptionInCommand($command)) {
                $arguments[] = '--job-id=' . $id;
            }

            $from_date = ($item->getExecutionFromDate())
                ?: ''
            ;
            $from_date_str = ($from_date instanceof DateTime)
                ? $from_date->format('Y-m-d')
                : $from_date
            ;
            $from_time = ($item->getExecutionFromTime())
                ?: ''
            ;
            $from_time_str = ($from_time instanceof DateTime)
                ? $from_time->format('H:i:s')
                : $from_time
            ;

            $from = new DateTimeImmutable($from_date_str . ' ' . $from_time_str, new DateTimeZone('Europe/Paris'));

            $until_date = ($item->getExecutionUntilDate())
                ?: ''
            ;

            $until_date_str = ($until_date instanceof DateTime)
                ? $until_date->format('Y-m-d')
                : $until_date
            ;

            $until_time = ($item->getExecutionUntilTime()) ?: '';
            $until_time_str = ($until_time instanceof DateTime)
                ? $until_time->format('H:i:s')
                : $until_time
            ;

            $until = ($until_date_str === '' && $until_time_str === '')
                ? new DateTimeImmutable('3000-01-01')
                : new DateTimeImmutable($until_date_str . ' ' . $until_time_str, new DateTimeZone('Europe/Paris'))
            ;

            switch ($item->getExecutionType()) {
                case 'every':
                    try {
                        $scheduler->add(
                            RecurringMessage::every(
                                $frequency,
                                new ExecuteConsoleCommand($command, $arguments, $log_file, $id, $no_db_log),
                                $from,
                                $until
                            )
                        )
                        ;
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                    break;

                case 'cron':
                    try {
                        $scheduler->add(
                            RecurringMessage::cron(
                                $frequency,
                                new ExecuteConsoleCommand($command, $arguments, $log_file, $id, $no_db_log)
                            )
                        )
                        ;
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                    break;
            }
        }
        return $scheduler;
    }

    private function hasJobIdOptionInCommand(string $command): bool
    {
        $command = $this->application->find($command);
        $reflectionClass = new ReflectionClass(get_class($command));

        try {
            $method = $reflectionClass->getMethod('configure');
            $method->invoke($command);

            return $command->getDefinition()->hasOption('job-id');
        } catch (ReflectionException $e) {
        }
        return false;
    }
}
