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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Provider;

use AllowDynamicProperties;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

#[AllowDynamicProperties] class MessengerQueueProvider
{

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
        $this->application = new Application($this->kernel);
        $this->application->setAutoExit(false);
    }

    public function listMessengerQueues(): array
    {
        $file = __DIR__ . '/../../../../../config/packages/messenger.yaml';

        if (!file_exists($file)) {
            return [];
        }

        $transportNames = [];
        try {
            $config = Yaml::parseFile($file);
            foreach ($config['framework']['messenger']['transports'] ?? [] as $name => $transport) {
                $transportNames[] = $name;
            }
        } catch (\Exception $e) {
            return [];
        }

        return $transportNames;
    }
}
