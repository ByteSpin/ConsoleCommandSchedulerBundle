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
        foreach($commands as $item) {
            $frequency = $item->getFrequency();
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

            $until_time = ($item->getExecutionUntilTime()) ?:'';
            $until_time_str = ($until_time instanceof DateTime)
                ? $until_time->format('H:i:s')
                : $until_time
            ;

            $until = ($until_date_str === '' && $until_time_str === '')
                ? new DateTimeImmutable('3000-01-01')
                : new DateTimeImmutable($until_date_str . ' ' . $until_time_str, new DateTimeZone('Europe/Paris'))
            ;

            switch($item->getExecutionType()) {
                case 'every':
                    try {
                        $scheduler->add(
                            RecurringMessage::every(
                                $frequency,
                                new ExecuteConsoleCommand($command, $arguments),
                                $from,
                                $until
                            )
                        )
                        ;
                    } catch (Exception $e) {

                    }
                    break;

                case 'cron':
                    try {
                        $scheduler->add(
                            RecurringMessage::cron(
                                $frequency,
                                new ExecuteConsoleCommand($command, $arguments)
                            )
                        )
                        ;
                    } catch (Exception $e) {

                    }
                    break;
            }
        }
        return $scheduler;
    }
}
