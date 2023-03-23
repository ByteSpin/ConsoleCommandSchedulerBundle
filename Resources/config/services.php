<?php

declare(strict_types=1);

use Flashmer\CommandSchedulerBundle\Command\AddCommand;
use Flashmer\CommandSchedulerBundle\Command\ExecuteCommand;
use Flashmer\CommandSchedulerBundle\Command\MonitorCommand;
use Flashmer\CommandSchedulerBundle\Command\RemoveCommand;
use Flashmer\CommandSchedulerBundle\Command\StartSchedulerCommand;
use Flashmer\CommandSchedulerBundle\Command\StopSchedulerCommand;
use Flashmer\CommandSchedulerBundle\Command\TestCommand;
use Flashmer\CommandSchedulerBundle\Command\UnlockCommand;
use Flashmer\CommandSchedulerBundle\Controller\DetailController;
use Flashmer\CommandSchedulerBundle\Controller\ApiController;
use Flashmer\CommandSchedulerBundle\Controller\ListController;
use Flashmer\CommandSchedulerBundle\Entity\ScheduledCommand;
use Flashmer\CommandSchedulerBundle\EventSubscriber\SchedulerCommandSubscriber;
use Flashmer\CommandSchedulerBundle\Form\Type\CommandChoiceType;
use Flashmer\CommandSchedulerBundle\Service\CommandParser;
use Flashmer\CommandSchedulerBundle\Command\ListCommand;
use Flashmer\CommandSchedulerBundle\Service\CommandSchedulerExecution;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire(true);

    $services->set(DetailController::class)
        ->call('setManagerRegistry', [service('doctrine')])
        ->call('setManagerName', ['%flashmer_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [service('translator')])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments');

    $services->set(ListController::class)
        ->call('setManagerRegistry', [service('doctrine')])
        ->call('setManagerName', ['%flashmer_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [service('translator')])
        ->call('setLockTimeout', ['%flashmer_command_scheduler.lock_timeout%'])
        ->call('setLogger', [service('logger')])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments');

    $services->set(CommandParser::class)
        ->args(
            [
                service('kernel'),
                '%flashmer_command_scheduler.excluded_command_namespaces%',
                '%flashmer_command_scheduler.included_command_namespaces%',
            ]
        );

    $services->set(ApiController::class)
        ->call('setManagerRegistry', [service('doctrine')])
        ->call('setManagerName', ['%flashmer_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [service('translator')])
        ->call('setLockTimeout', ['%flashmer_command_scheduler.lock_timeout%'])
        ->call('setLogger', [service('logger')])
        ->call('setCommandParser', [service(CommandParser::class)])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments')
    ;

    $services->set(CommandSchedulerExecution::class)
        ->args(
            [
                service('kernel'),
                service('parameter_bag'),
                service('logger'),
                service('event_dispatcher'),
                service('doctrine'),
                '%flashmer_command_scheduler.doctrine_manager%',
                '%flashmer_command_scheduler.log_path%',
            ]
        )
        #->alias("CommandSchedulerExecution")
    ;

    $services->set(CommandChoiceType::class)
        ->tag('form.type', ['alias' => 'command_choice']);

    $services->set(ExecuteCommand::class)
        ->args(
            [
                service(CommandSchedulerExecution::class),
                service('event_dispatcher'),
                service('doctrine'),
                '%flashmer_command_scheduler.doctrine_manager%',
                '%flashmer_command_scheduler.log_path%',
            ]
        )
        ->tag('console.command');

    $services->set(MonitorCommand::class)
        ->args(
            [
                service('event_dispatcher'),
                service('doctrine'),
                '%flashmer_command_scheduler.doctrine_manager%',
                '%flashmer_command_scheduler.lock_timeout%',
                '%flashmer_command_scheduler.monitor_mail%',
                '%flashmer_command_scheduler.monitor_mail_subject%',
                '%flashmer_command_scheduler.send_ok%',
            ]
        )
        ->tag('console.command');

    $services->set(ListCommand::class)
        ->args(
            [
                service('doctrine'),
                '%flashmer_command_scheduler.doctrine_manager%'
            ]
        )
        ->tag('console.command');

    $services->set(UnlockCommand::class)
        ->args(
            [
                service('doctrine'),
                '%flashmer_command_scheduler.doctrine_manager%',
                '%flashmer_command_scheduler.lock_timeout%',
            ]
        )
        ->tag('console.command');

    $services->set(AddCommand::class)
        ->args(
            [
                service('doctrine'),
                '%flashmer_command_scheduler.doctrine_manager%',
            ]
        )
        ->tag('console.command');

    $services->set(RemoveCommand::class)
        ->args(
            [
                service('doctrine'),
                '%flashmer_command_scheduler.doctrine_manager%',
            ]
        )
        ->tag('console.command');

    $services->set(StartSchedulerCommand::class)
        ->tag('console.command');

    $services->set(StopSchedulerCommand::class)
        ->tag('console.command');

    $services->set(TestCommand::class)
        ->tag('console.command');

    $services->set(ScheduledCommand::class)
        ->tag('controller.service_arguments');


    if(class_exists(Symfony\Component\Notifier\NotifierInterface::class))
    {$notifier = service('notifier');}
    else { $notifier = null; }

    $services->set(SchedulerCommandSubscriber::class)
        ->args(
            [
                service('logger'),
                service('doctrine.orm.default_entity_manager'),
                $notifier,
                '%flashmer_command_scheduler.monitor_mail%',
                '%flashmer_command_scheduler.monitor_mail_subject%',
            ]
        )
        ->tag('kernel.event_subscriber');
};
