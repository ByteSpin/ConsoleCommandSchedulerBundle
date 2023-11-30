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

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class BundleVersionProvider
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getBundleVersion(string $bundleName = 'bytespin/console-command-scheduler-bundle'): ?string
    {
        return $this->cache->get('bytespin_console_command_scheduler_bundle_version', function (ItemInterface $item) use ($bundleName) {
            $composerLock = json_decode(file_get_contents($this->projectDir . '/composer.lock'), true);

            $packages = $composerLock['packages'] ?? [];

            $filtered = array_filter($packages, function ($package) use ($bundleName) {
                return $package['name'] === $bundleName;
            });

            $package = reset($filtered);
            return $package ? $package['version'] : null;
        });
    }
}
