Copyright (c) 2023 Greg LAMY <greg@bytespin.net>

This is a public project hosted on GitHub : https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git

This bundle was originally developed as part of an ETL project.


ByteSpin/ConsoleCommandSchedulerBundle is a Symfony 6.3 simple bundle that allows you to schedule console commands easily:
- Use the latest messenger/scheduler Symfony 6.3+ components,
- Log all console commands data (last execution time, duration, return code) in database and log file,
- An admin interface is available with the help of EasyCorp/EasyAdmin bundle

> [!NOTE]
>
> This project is still at alpha state and has not yet been fully tested outside its parent project.
> 
> **Feel free to submit bug and/or pull requests!**

just keep in mind that I want to keep it as simple as possible!

Requirements
------------
- php 8.2+
- Symfony 6.3+

Installation
------------

First install the bundle:
```
composer require bytespin/console-commande-scheduler-bundle
```

Then updates the database schema:
```
php bin/console doctrine:schema:update --force
```

Administration interface
------------------------

> [!NOTE]
>
> Please note that the administration interface is based on EasyAdmin symfony bundle.
> 
> Because you might already use EasyAdmin in your project, no DashboardController is provided with the bundle.
> 
> If you don't have one, generate it with ```bin/console make:admin:dashboard```

You need to manually add the menu to your DashboardController.php file, for example:

```
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\SchedulerLog;
use ByteSpin\ConsoleCommandSchedulerBundle\Controller\Admin\SchedulerCrudController;
use ByteSpin\ConsoleCommandSchedulerBundle\Controller\Admin\SchedulerLogCrudController;

(...)

yield MenuItem::subMenu('Symfony Scheduler', 'fa-duotone fa-folder-gear')->setSubItems([
    MenuItem::linkToCrud('Scheduled Tasks', 'fa-light fa-clock', Scheduler::class)->setController(SchedulerCrudController::class),
    MenuItem::linkToCrud('Logs', 'fa-duotone fa-clock-rotate-left', SchedulerLog::class)->setController(SchedulerLogCrudController::class),
]);
```
(The previous lines make use of FontAwesome icons. You are free to use any other solution)

Usage
-----
> [!NOTE]
> The bundle makes use of the very new symfony/scheduler component that is said to be experimental on 6.3 symfony version
> 
> That will change in the forthcoming 6.4 release. 
> 
> The only documentation available for the moment is on the official symfony blog, with some useful examples.
> Please read it carefully at https://symfony.com/blog/new-in-symfony-6-3-scheduler-component
> 
> The 'from_date', 'from_time', 'until_date', 'until_time' bundle parameters are used to construct the expected scheduler trigger

The administration interface provides two sections:

- **<u>The main console command scheduler section:</u>**

    - When you click on the menu, EasyAdmin provides the default list view for Console Commands  Scheduler
      ![List Console Command Scheduler section](docs/images/console_command_list.png)

    - You can add/view/edit any entry in this list:
      ![Edit Console Command Scheduler section](docs/images/console_command_edit.png)
      
      - **Disabled**: if checked, the Console Command will be ignored by the scheduler 
      - **Command**: this field lists all available console commands defined in your symfony project
      - **Arguments**: provide any console command arguments if needed, separated by a space, as if you were typing them on the command line
      - **Type**: here you select one of the two symfony/scheduler supported command type 
        - 'Frequency' will generate a trigger of the RecurringMessage::every form,
        - 'Cron' = will generate a trigger of the RecurringMessage::cron form
      - **Frequency**: here you type the desired frequency
        - If Type is 'Frequency' ; for example '10 seconds', '1 day', 'first monday of next month' (refer to the doc)
        - If Type is 'Cron' ; use any cron expression
      - **From Date, From Time, Until Date, Until Time** are used to generate the trigger. They are ignored in case of 'Cron' Type. 
      - **Log File**: you can provide the log file name desired for the current command.
        - Please note that you must not provide the full path, only the log filename.
        - If not provided, a default %env%_scheduler.log is created.
 

- **<u>The log section:</u>** provides a simple log viewing interface

  ![List Log section](docs/images/console_command_log.png)

Consuming messages
------------------
The standard way of consuming scheduler messages is

```bin/console messenger:consume scheduler_scheduler```

If you want the command to be verbose, please use:
```bin/console messenger:consume scheduler_scheduler -vv```

You can use cron or supervisor to achieve this ; The console commands are then executed according to the generated triggers.

The commands returning code, date and duration are logged in the dedicated table and in a dedicated log

You can view the logs in the administration interface

Licence
-------

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


