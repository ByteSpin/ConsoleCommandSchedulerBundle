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

namespace ByteSpin\ConsoleCommandSchedulerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigPathCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        dump('ByteSpin Compiler Pass Execution');
        if (!$container->hasDefinition('twig.loader.filesystem')) {
            return;
        }

        $definition = $container->getDefinition('twig.loader.filesystem');
        $definition->addMethodCall('addPath', [__DIR__ . '/../../templates', 'ByteSpinConsoleCommandSchedulerBundle']);
    }
}
