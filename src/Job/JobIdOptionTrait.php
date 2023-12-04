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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

trait JobIdOptionTrait
{
    protected function configureJobIdOption(): void
    {
        /* @var Command $this */
        $this->addOption('job-id', null, InputOption::VALUE_REQUIRED, 'Job ID');
    }
}
