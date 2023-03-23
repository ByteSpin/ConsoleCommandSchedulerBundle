<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('flashmer_command_scheduler_list', '/command-scheduler/list')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ListController::indexAction']);

    $routingConfigurator->add('flashmer_command_scheduler_monitor', '/command-scheduler/monitor')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ApiController::monitorAction']);

    $routingConfigurator->add('flashmer_command_scheduler_api_list', '/command-scheduler/api/list')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ApiController::listAction']);

    $routingConfigurator->add('flashmer_command_scheduler_api_console_commands', '/command-scheduler/api/console_commands')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ApiController::getConsoleCommands']);

    $routingConfigurator->add('flashmer_command_scheduler_api_console_commands_details', '/command-scheduler/api/console_commands_details/{commands}')
        ->defaults(
            ['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ApiController::getConsoleCommandsDetails',
             'commands' => 'all'
            ]);

    $routingConfigurator->add('flashmer_command_scheduler_api_translate_cron_expression',
            '/command-scheduler/api/trans_cron_expression/{cronExpression}/{lang}')
        ->defaults(
            ['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ApiController::translateCronExpression',
                'lang' => 'en'
            ]);

    $routingConfigurator->add('flashmer_command_scheduler_action_toggle', '/command-scheduler/action/toggle/{id}')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ListController::toggleAction']);

    $routingConfigurator->add('flashmer_command_scheduler_action_remove', '/command-scheduler/action/remove/{id}')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ListController::removeAction']);

    $routingConfigurator->add('flashmer_command_scheduler_action_execute', '/command-scheduler/action/execute/{id}')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ListController::executeAction']);

    $routingConfigurator->add('flashmer_command_scheduler_action_unlock', '/command-scheduler/action/unlock/{id}')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\ListController::unlockAction']);

    $routingConfigurator->add('flashmer_command_scheduler_detail_edit', '/command-scheduler/detail/edit/{id}')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\DetailController::edit']);

    $routingConfigurator->add('flashmer_command_scheduler_detail_new', '/command-scheduler/detail/edit')
        ->defaults(['_controller' => 'Flashmer\CommandSchedulerBundle\Controller\DetailController::edit']);
};
