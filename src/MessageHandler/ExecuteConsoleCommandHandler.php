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

namespace ByteSpin\ConsoleCommandSchedulerBundle\MessageHandler;

use DateTime;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\ExecuteConsoleCommand;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\LogConsoleCommand;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;

#[AsMessageHandler]
final readonly class ExecuteConsoleCommandHandler
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%kernel.logs_dir%')]
        private string $logDir,
        #[Autowire('%kernel.environment%')]
        private string $environment,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(ExecuteConsoleCommand $message): void
    {
        $logFile = $message->logFile
            ? $this->logDir . '/' . $this->environment . '_' . $message->logFile
            : $this->logDir . '/' . $this->environment . '_scheduler.log'
        ;

        $process = new Process([
            $this->projectDir . '/bin/console',
            $message->command,
            ...$message->commandArguments
        ]);

        // start time for duration calculation
        $start = microtime(true);
        $dateTime = new DateTime();

        $process->start();

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                file_put_contents($logFile, $data, FILE_APPEND);
            } else { // $process::ERR === $type
                file_put_contents($logFile, $data, FILE_APPEND);
            }
        }

        $process->wait();

        // duration
        $duration = round((microtime(true) - $start), 2);

        // dispatch execution message
        $message = new LogConsoleCommand(
            $message->command,
            $message->commandArguments,
            $dateTime,
            $duration,
            $process->getExitCode(),
        );

        $this->eventDispatcher->dispatch(new GenericEvent($message, [
        ]), 'log.scheduled.console.command');


        if ($process->getExitCode() === 0) {

            file_put_contents($logFile, "Command executed successfully\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "Command failure: " . $process->getExitCode() . "\n", FILE_APPEND);
        }
    }
}
