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

namespace ByteSpin\ConsoleCommandSchedulerBundle\DependencyInjection;

use ByteSpin\ConsoleCommandSchedulerBundle\Model\TagInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConsoleCommandSchedulerExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Store tags configuration as parameters
        $container->setParameter('bytespin_scheduler.tags.enabled', $config['tags']['enabled']);
        $container->setParameter('bytespin_scheduler.tags.class', $config['tags']['class']);
        $container->setParameter('bytespin_scheduler.tags.crud_controller', $config['tags']['crud_controller']);

        // Validate tag class if enabled
        if ($config['tags']['enabled']) {
            if (empty($config['tags']['class'])) {
                throw new \InvalidArgumentException('The "bytespin_console_command_scheduler.tags.class" configuration is required when tags are enabled.');
            }

            // Check if the class implements TagInterface (will be validated at runtime)
            $tagClass = $config['tags']['class'];
            if (class_exists($tagClass) && !is_subclass_of($tagClass, TagInterface::class)) {
                throw new \InvalidArgumentException(sprintf('The tag class "%s" must implement "%s".', $tagClass, TagInterface::class));
            }
        }

        // Load services configuration
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'bytespin_console_command_scheduler';
    }
}
