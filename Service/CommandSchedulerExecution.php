<?php

namespace Flashmer\CommandSchedulerBundle\Service;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Flashmer\CommandSchedulerBundle\Entity\ScheduledCommand;
use Flashmer\CommandSchedulerBundle\Entity\ScheduledCommandHistory;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandPostExecutionEvent;
use Flashmer\CommandSchedulerBundle\Event\SchedulerCommandPreExecutionEvent;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Command\Command;
use Throwable;



class CommandSchedulerExecution
{
    private string $env;
    private null|string $logPath;
    private EntityManagerInterface $em;
    private Application $application;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        KernelInterface          $kernel,
        ParameterBagInterface    $parameterBag,
        EventDispatcherInterface $eventDispatcher,
        EntityManager            $entityManager
    )
    {
        $this->em = $entityManager;
        $this->logPath = $parameterBag->get('command_scheduler.log_path');
        $this->eventDispatcher = $eventDispatcher;
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }


    private function getCommand(ScheduledCommand $scheduledCommand): ?Command
    {
        try {
            $command = $this->application->find($scheduledCommand->getCommand());
        } catch (InvalidArgumentException) {

            return null;
        }

        return $command;
    }

    private function getLog(
        ScheduledCommand $scheduledCommand,
        int $commandsVerbosity = OutputInterface::OUTPUT_NORMAL
    ): OutputInterface
    {
        // Use a StreamOutput or NullOutput to redirect write() and writeln() in a log file
        if (!$this->logPath || empty($scheduledCommand->getLogFile())) {
            $logOutput = new NullOutput();
        } else {
            // log into a file
            $logOutput = new StreamOutput(
                fopen(
                    $this->logPath.$scheduledCommand->getLogFile(),
                    'ab'
                ),
                $commandsVerbosity
            );
        }

        return $logOutput;
    }

    /**
     * - Find command
     */
    private function prepareCommandExecution(ScheduledCommand $scheduledCommand): ?Command
    {
        if(!($command = $this->getCommand($scheduledCommand)))
        {
            $scheduledCommand->setLastReturnCode(-1);
        }

        return $command;
    }


    /**
     * Get Input Command
     * - call the command with args and environment
     * - merge the definition of the commands
     * - Disable interactive mode
     * @noinspection PhpInternalEntityUsedInspection
     */
    private function getInputCommand(ScheduledCommand $scheduledCommand, Command $command, string $env): StringInput
    {
        $inputCommand = new StringInput(
            $scheduledCommand->getCommand().' '.$scheduledCommand->getArguments().' --env='.$env
        );

        $command->mergeApplicationDefinition();
        $inputCommand->bind($command->getDefinition());

        // Disable interactive mode if the current command has no-interaction flag
        if ($inputCommand->hasParameterOption(['--no-interaction', '-n'])) {
            $inputCommand->setInteractive(false);
        }

        return $inputCommand;
    }


    /**
     * Do the real execution of a command
     */
    private function doExecution(ScheduledCommand $scheduledCommand, int $commandsVerbosity): int
    {
        $command = $this->prepareCommandExecution($scheduledCommand);

        $input = $this->getInputCommand($scheduledCommand, $command, $this->env);

        $logOutput = $this->getLog($scheduledCommand, $commandsVerbosity);

        $startRun = new DateTimeImmutable();
        $exception = null;
        $result = null;

        // Execute command and get return code
        try {
            $this->eventDispatcher->dispatch(new SchedulerCommandPreExecutionEvent($scheduledCommand));
            $result = $command->run($input, $logOutput);

            $this->em->clear();
        } catch (Throwable $e) {
            $exception = $e;
            $logOutput->writeln($e->getMessage());
            $logOutput->writeln($e->getTraceAsString());
            $result = -1;
        } finally {
            $endRun = new DateTimeImmutable();

            $profiling = [
                "startRun" => $startRun,
                "endRun"   => $endRun,
                "runtime" => $startRun->diff($endRun),
            ];

            $this->eventDispatcher->dispatch(new SchedulerCommandPostExecutionEvent($scheduledCommand, $result, $logOutput, $profiling, $exception));
        }

        return $result;
    }


    /**
     * @throws Exception
     */
    private function prepareExecution(ScheduledCommand $scheduledCommand): void
    {
        //reload command from database before every execution to avoid parallel execution
        $this->em->getConnection()->beginTransaction();
        try {
            $scheduledCommandRepository = $this->em->getRepository(ScheduledCommand::class);
            $notLockedCommand = $scheduledCommandRepository->getNotLockedCommand($scheduledCommand);

            //$notLockedCommand will be locked for avoiding parallel calls:
            // http://dev.mysql.com/doc/refman/5.7/en/innodb-locking-reads.html
            if (null === $notLockedCommand) {
                throw new RuntimeException();
            }

            $scheduledCommand = $notLockedCommand;
            $scheduledCommand->setLastExecution(new DateTime());
            $scheduledCommand->setLocked(true);
            $this->em->persist($scheduledCommand);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (Throwable) {
            $this->em->getConnection()->rollBack();
            return;
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws Exception
     */
    public function executeCommand(
        ScheduledCommand $scheduledCommand,
        string $env,
        string|int $commandsVerbosity = OutputInterface::VERBOSITY_NORMAL):int
    {
        $this->env = $env;
        $this->prepareExecution($scheduledCommand);

        /** @var ScheduledCommand $scheduledCommand */
        $scheduledCommand = $this->em->find(ScheduledCommand::class, $scheduledCommand);

        $result = $this->doExecution($scheduledCommand, $commandsVerbosity);

        // Reactivate the command in DB
        /** @var ScheduledCommand $scheduledCommand */
        $scheduledCommand = $this->em->find(ScheduledCommand::class, $scheduledCommand);

        // calculate duration
        $startDateTime = $scheduledCommand->getLastExecution();
        $dateDiff = $startDateTime->diff(new DateTime());
        if ($dateDiff->h == 0 && $dateDiff->i == 0) {
            $lastDuration = $dateDiff->s.' sec.';
        }
        elseif ($dateDiff->h == 0 && $dateDiff->i != 0) {
            $lastDuration = $dateDiff->i.' min. '.$dateDiff->s.' sec.';
        }
        else {
            $lastDuration = $dateDiff->h.' h '.$dateDiff->i.' min. '.$dateDiff->s.' sec.';
        }

        $scheduledCommand->setLastDuration($lastDuration);
        $scheduledCommand->setLastReturnCode($result);
        $scheduledCommand->setLocked(false);
        $scheduledCommand->setExecuteImmediately(false);

        // log data
        $scheduledCommandHistory = new ScheduledCommandHistory();
        $scheduledCommandHistory->setName($scheduledCommand->getName());
        $scheduledCommandHistory->setCommand($scheduledCommand->getCommand().' '.$scheduledCommand->getArguments());
        $scheduledCommandHistory->setDate($scheduledCommand->getLastExecution());
        $scheduledCommandHistory->setDuration($scheduledCommand->getLastDuration());
        $scheduledCommandHistory->setReturnCode($scheduledCommand->getLastReturnCode());

        try {
            $this->em->persist($scheduledCommand);
            $this->em->persist($scheduledCommandHistory);
            $this->em->flush();

            /*
             * This clear() is necessary to avoid conflict between commands and to be sure that none entity are managed
             * before entering a new command
             */
            $this->em->clear();
        }
        catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }

        unset($command);
        gc_collect_cycles();

        return $result;
    }
}
