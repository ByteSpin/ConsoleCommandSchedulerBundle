<?php

/**
 * This file is part of the ByteSpin/ConsoleCommandSchedulerBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git.
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ByteSpin\ConsoleCommandSchedulerBundle\Scheduler;

use ByteSpin\ConsoleCommandSchedulerBundle\Event\SchedulerEntryFaultedEvent;
use ByteSpin\ConsoleCommandSchedulerBundle\Factory\RecurringMessageFactory;
use ByteSpin\ConsoleCommandSchedulerBundle\Repository\SchedulerRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('scheduler')]
final class ConsoleJobsScheduler implements ScheduleProviderInterface
{
    /**
     * Faults of the last schedule build, keyed by entry id — read by the
     * admin list to badge invalid entries.
     */
    public const FAULTS_CACHE_KEY = 'bytespin_scheduler_build_faults';

    public function __construct(
        private readonly SchedulerRepository $schedulerRepository,
        private readonly RecurringMessageFactory $recurringMessageFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CacheInterface $cache,
        private readonly LockFactory $lockFactory,
        private readonly ?LoggerInterface $logger = null,
        private readonly bool $processOnlyLastMissedRun = true,
    ) {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();
        $faults = [];

        foreach ($this->schedulerRepository->findBy(['disabled' => false]) as $entry) {
            try {
                $schedule->add($this->recurringMessageFactory->create($entry));
            } catch (\Throwable $error) {
                // One faulty entry must NEVER take the whole schedule down:
                // it is skipped loudly (log + event) and every other entry
                // keeps running.
                $faults[(int) $entry->getId()] = $error->getMessage();
                $this->logger?->error(sprintf(
                    '[ConsoleCommandScheduler] %s — entry skipped, the rest of the schedule keeps running.',
                    $error->getMessage(),
                ));
                $this->eventDispatcher->dispatch(
                    new SchedulerEntryFaultedEvent($entry, $error),
                    SchedulerEntryFaultedEvent::NAME,
                );
            }
        }

        $this->storeFaults($faults);

        return $schedule
            ->stateful($this->cache)
            ->processOnlyLastMissedRun($this->processOnlyLastMissedRun)
            ->lock($this->lockFactory->createLock('scheduler_scheduler'))
        ;
    }

    /**
     * @param array<int, string> $faults
     */
    private function storeFaults(array $faults): void
    {
        // CacheInterface has no unconditional set: delete + recompute.
        $this->cache->delete(self::FAULTS_CACHE_KEY);
        $this->cache->get(self::FAULTS_CACHE_KEY, static fn (): array => $faults);
    }
}
