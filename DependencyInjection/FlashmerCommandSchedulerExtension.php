<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Flashmer\CommandSchedulerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}.
 *
 * @see https://symfony.com/doc/current/bundles/configuration.html
 */
class FlashmerCommandSchedulerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Default
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        foreach ($config as $key => $value) {
            $container->setParameter('flashmer_command_scheduler.'.$key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'flashmer_command_scheduler';
    }
}
