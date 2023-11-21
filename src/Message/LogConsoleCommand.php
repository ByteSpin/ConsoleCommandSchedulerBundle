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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Message;

use DateTime;
use DateTimeInterface;

final readonly class LogConsoleCommand
{
    public function __construct(
        public string $command,
        public array  $commandArguments = [],
        public DateTimeInterface|null $start = new DateTime(),
        public float $duration = 0,
        public int|null $return_code = null,
    ) {
    }
}
