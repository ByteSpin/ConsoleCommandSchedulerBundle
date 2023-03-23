<?php

namespace Flashmer\CommandSchedulerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Flashmer\CommandSchedulerBundle\DependencyInjection\FlashmerCommandSchedulerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;

/**
 * Class FlashmerCommandSchedulerBundle.
 */
class FlashmerCommandSchedulerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $ormCompilerClass = DoctrineOrmMappingsPass::class;

        if (class_exists($ormCompilerClass))
        {
            $namespaces = ['Flashmer\CommandSchedulerBundle\Entity'];
            $directories = [realpath(__DIR__.'/Entity')];
            $managerParameters = [];
            $enabledParameter = false;
            $aliasMap = ['CommandSchedulerBundle' => 'Flashmer\CommandSchedulerBundle\Entity'];

            $driver = new Definition(AttributeDriver::class, [$directories]);

            $container->addCompilerPass(
                new DoctrineOrmMappingsPass(
                    $driver,
                    $namespaces,
                    $managerParameters,
                    $enabledParameter,
                    $aliasMap
                )
            );

                # TODO
            /** If this is merged it could be renamed https://github.com/doctrine/DoctrineBundle/pull/1369/files
             * new DoctrineOrmMappingsPass(
             * DoctrineOrmMappingsPass::createPhpMappingDriver(
             * $namespaces,
            $directories,
            $managerParameters,
            $enabledParameter,
            $aliasMap)
             */
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): FlashmerCommandSchedulerExtension
    {
        $class = $this->getContainerExtensionClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensionClass(): string
    {
        return FlashmerCommandSchedulerExtension::class;
    }
}
