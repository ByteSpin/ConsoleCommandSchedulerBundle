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

use ByteSpin\ConsoleCommandSchedulerBundle\EventSubscriber\TagMappingListener;
use ByteSpin\ConsoleCommandSchedulerBundle\Model\TagInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConsoleCommandSchedulerExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        if ($config['tags']['enabled'] && !empty($config['tags']['class'])) {
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'resolve_target_entities' => [
                        TagInterface::class => $config['tags']['class'],
                    ],
                ],
            ]);
        }
    }

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

        // Validate and register tag mapping if enabled
        if ($config['tags']['enabled']) {
            if (empty($config['tags']['class'])) {
                throw new \InvalidArgumentException('The "bytespin_console_command_scheduler.tags.class" configuration is required when tags are enabled.');
            }

            $tagClass = $config['tags']['class'];
            if (class_exists($tagClass) && !is_subclass_of($tagClass, TagInterface::class)) {
                throw new \InvalidArgumentException(sprintf('The tag class "%s" must implement "%s".', $tagClass, TagInterface::class));
            }

            // Register the listener that dynamically adds the ManyToMany mapping
            $definition = new Definition(TagMappingListener::class, [$tagClass]);
            $definition->addTag('doctrine.event_listener', ['event' => 'loadClassMetadata']);
            $container->setDefinition(TagMappingListener::class, $definition);
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
