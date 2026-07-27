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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bytespin_console_command_scheduler');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('scheduler')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('process_only_last_missed_run')
                            ->defaultTrue()
                            ->info('When the scheduler resumes after a downtime, run at most ONE catch-up occurrence per task instead of replaying every missed run (cron-like semantics). Set to false to restore the pre-2.1 replay-everything behavior.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('tags')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('Enable tag support for scheduled commands')
                        ->end()
                        ->scalarNode('class')
                            ->defaultNull()
                            ->info('The fully qualified class name of the Tag entity (must implement TagInterface)')
                        ->end()
                        ->scalarNode('crud_controller')
                            ->defaultNull()
                            ->info('The fully qualified class name of the Tag CRUD controller (for autocomplete creation)')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
