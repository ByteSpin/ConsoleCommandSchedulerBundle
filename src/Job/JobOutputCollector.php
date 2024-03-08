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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Job;

class JobOutputCollector
{
    public array $outputs = [];

    public function addOutput($commandId, $output): void
    {
        $this->outputs[$commandId][] = $output;
    }

    public function getOutputs(int $commandId)
    {
        return $this->outputs[$commandId] ?? null;
    }

    public function clearOutputs(?int $commandId = null): void
    {
        if ($commandId === null) {
            $this->outputs = [];
        } else {
            unset($this->outputs[$commandId]);
        }
    }
}
