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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Factory;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

final class ConsoleCommandIntrospector implements CommandIntrospectorInterface
{
    private Application $application;

    public function __construct(KernelInterface $kernel)
    {
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }

    public function hasJobIdOption(string $command): bool
    {
        $command = $this->application->find($command);
        $reflectionClass = new \ReflectionClass($command::class);

        try {
            $method = $reflectionClass->getMethod('configure');
            $method->invoke($command);

            return $command->getDefinition()->hasOption('job-id');
        } catch (\ReflectionException) {
            return false;
        }
    }
}
