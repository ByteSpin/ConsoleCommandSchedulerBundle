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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Job;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

readonly class JobOutputCollector
{
    public function __construct(
        private CacheItemPoolInterface $cachePool,
    ) {
    }

    /**
     * @param array<string, mixed> $output
     *
     * @throws InvalidArgumentException
     */
    public function addOutput(int|string $commandId, array $output): void
    {
        $item = $this->cachePool->getItem((string) $commandId);

        $outputs = $item->isHit() ? $item->get() : [];
        $outputs[] = $output;

        $item->set($outputs);
        $this->cachePool->save($item);
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws InvalidArgumentException
     */
    public function getOutputs(int $commandId): array
    {
        $item = $this->cachePool->getItem((string) $commandId);
        if ($item->isHit()) {
            return $item->get();
        }

        return [];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function clearOutputs(int $commandId): void
    {
        $this->cachePool->deleteItem((string) $commandId);
    }
}
