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

namespace ByteSpin\ConsoleCommandSchedulerBundle\MessageHandler;

use DateTime;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\ExecuteConsoleCommand;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\LogConsoleCommand;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
        // deactivate timeout
        //todo: add timeout field in scheduler table
        $process->setTimeout(null);

        // start time for duration calculation
        $start = microtime(true);
        $dateTime = new DateTime();

        try {
            $process->start();

            foreach ($process as $type => $data) {
                // keep common parts for further use
                // distinguish error and standard log?
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
        } catch (ProcessFailedException $e) {
            file_put_contents($logFile, "Command failure: " . $e->getMessage() . "\n", FILE_APPEND);
        }

    }
}
