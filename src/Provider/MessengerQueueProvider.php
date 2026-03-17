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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Provider;

use Symfony\Contracts\Service\ServiceCollectionInterface;

class MessengerQueueProvider
{
    /**
     * @param ServiceCollectionInterface<mixed> $locator
     */
    public function __construct(
        private readonly ServiceCollectionInterface $locator,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function listMessengerQueues(): array
    {
        $transportNames = [];

        foreach ($this->locator as $serviceId => $service) {
            if (!str_contains($serviceId, 'messenger.') && !str_contains($serviceId, 'failed')) {
                $transportNames[$serviceId] = $serviceId;
            }
        }

        return $transportNames;
    }
}
