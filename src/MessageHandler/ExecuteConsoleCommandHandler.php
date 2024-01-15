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

use ByteSpin\ConsoleCommandSchedulerBundle\Event\ScheduledConsoleCommandGenericEvent;
use ByteSpin\ConsoleCommandSchedulerBundle\Message\ExecuteConsoleCommand;
use DateTime;
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

        $errorLogFile = $message->logFile
            ? $this->logDir . '/' . $this->environment . '_error_' . $message->logFile
            : $this->logDir . '/' . $this->environment . '_error_scheduler.log'
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
        $start = time();

        // dispatch before execution event
        $this->eventDispatcher->dispatch(new GenericEvent(
            new ScheduledConsoleCommandGenericEvent(
                $message->command,
                $message->commandArguments,
                (new DateTime())->setTimestamp($start),
                (new DateTime('1990-01-01')),
                '',
                null,
                null,
                $message->id,
                $message->noDbLog,
            ),
            []
        ), 'bytespin.before.scheduled.console.command');

        try {
            $process->start();

            foreach ($process as $type => $data) {
                // keep common parts for further use
                // distinguish error and standard log?
                if ($process::OUT === $type) {
                    file_put_contents($logFile, $data, FILE_APPEND);
                } else { // $process::ERR === $type
                    file_put_contents($errorLogFile, $data, FILE_APPEND);
                }
            }

            file_put_contents($logFile, $process->getOutput(), FILE_APPEND);

            $process->wait();

            // end & duration
            $end = time();
            $duration = $end - $start;

            // update message with execution data
            $message = new ScheduledConsoleCommandGenericEvent(
                $message->command,
                $message->commandArguments,
                (new DateTime())->setTimestamp($start),
                (new DateTime())->setTimestamp($end),
                $this->durationConverter($duration),
                $process->getExitCode(),
                $logFile,
                $message->id,
                $message->noDbLog,
            );

            // dispatch log event ($event content is the same)
            if (true !== $message->noDbLog) {
                $this->eventDispatcher->dispatch(new GenericEvent(
                    $message,
                    []
                ), 'bytespin.log.scheduled.console.command');
            }

            // dispatch success / failure event
            match ($process->getExitCode()) {
                0 => $this->eventDispatcher->dispatch(new GenericEvent(
                    $message,
                    []
                ), 'bytespin.success.scheduled.console.command'),

                default => $this->eventDispatcher->dispatch(new GenericEvent(
                    $message,
                    []
                ), 'bytespin.failure.scheduled.console.command'),
            };

            // dispatch after execution event
            $this->eventDispatcher->dispatch(new GenericEvent(
                $message,
                []
            ), 'bytespin.after.scheduled.console.command');

            $messageLog = $message->command . ' ' . implode(' ', $message->commandArguments);

            if ($process->getExitCode() === 0) {
                file_put_contents(
                    $logFile,
                    'Command ' . $messageLog . ' executed successfully in ' . $duration . ' seconds' . PHP_EOL,
                    FILE_APPEND
                );
            } else {
                file_put_contents(
                    $logFile,
                    'Command ' . $messageLog . ' failure: ' . $process->getExitCode() . PHP_EOL,
                    FILE_APPEND
                );
            }
        } catch (ProcessFailedException $e) {
            file_put_contents(
                $logFile,
                'Command failure: ' . $e->getMessage() . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    private function durationConverter(int $seconds): string
    {
        $s = ($seconds < 1) ? 1 : $seconds;
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        $rs = $s % 60;

        $result = [];
        if ($h > 0) {
            $result[] = "$h h";
        }
        if ($m > 0) {
            $result[] = "$m min.";
        }
        if ($rs > 0 || count($result) == 0) {
            $result[] = "$rs sec.";
        }

        return implode(' ', $result);
    }
}
