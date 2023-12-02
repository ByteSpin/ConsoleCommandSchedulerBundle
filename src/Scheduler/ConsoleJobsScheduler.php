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

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\ExecuteConsoleCommand;
use ByteSpin\ConsoleCommandSchedulerBundle\Repository\SchedulerRepository;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('scheduler')]
final readonly class ConsoleJobsScheduler implements ScheduleProviderInterface
{
    public function __construct(
        private SchedulerRepository $schedulerRepository,
    ) {
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
            $arguments = ($item->getArguments())
                ? explode(' ', $item->getArguments())
                : []
            ;
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
                                new ExecuteConsoleCommand($command, $arguments, $log_file),
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
                                new ExecuteConsoleCommand($command, $arguments, $log_file)
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
}
