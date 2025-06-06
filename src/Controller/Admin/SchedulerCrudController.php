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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Controller\Admin;

use AllowDynamicProperties;
use ByteSpin\ConsoleCommandSchedulerBundle\Provider\BundleVersionProvider;
use ByteSpin\ConsoleCommandSchedulerBundle\Provider\MessengerQueueProvider;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Provider\ConsoleCommandProvider;
use Psr\Cache\InvalidArgumentException;

#[AllowDynamicProperties] class SchedulerCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ConsoleCommandProvider $consoleCommandProvider,
        private readonly BundleVersionProvider $bundleVersionProvider,
        private readonly MessengerQueueProvider $messengerQueueProvider,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Scheduler::class;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Console Command')
            ->setEntityLabelInPlural('Console Commands')
            ->setHelp('index', 'Bundle version ' . $this->bundleVersionProvider->getBundleVersion());
    }


    /**
     * @throws Exception
     */
    public function configureFields(string $pageName): iterable
    {

        return [
            IdField::new('id', 'ID')->hideOnForm()->setSortable(false)->hideOnIndex(),
            ChoiceField::new('command')->setChoices($this->consoleCommandProvider->listConsoleCommands()),
            ChoiceField::new('messenger_queue')->setChoices($this->messengerQueueProvider->listMessengerQueues()),
            TextField::new('arguments'),
            ChoiceField::new('execution_type', 'Type')->setChoices([
                'Frequency' => 'every',
                'Cron' => 'cron',
            ]),
            TextField::new('frequency'),
            DateField::new('execution_from_date')->setEmptyData('')->setFormat('yyyy-MM-dd')->setLabel('From Date'),
            TimeField::new('execution_from_time')->setEmptyData('')->setFormat('HH:mm')->setLabel('From Time'),
            DateField::new('execution_until_date')->setEmptyData('')->setFormat('yyyy-MM-dd')->setLabel('Until Date'),
            TimeField::new('execution_until_time')->setEmptyData('')->setFormat('HH:mm')->setLabel('Until Time'),
            BooleanField::new('disabled'),
            BooleanField::new('no_db_log')->setLabel('No Database Log'),
            TextField::new('log_file')
                ->setLabel('Log file')->setHelp('Do not provide the full path, only the log filename'),
            BooleanField::new('send_email')->setLabel('Send Notification?'),
            TextField::new('email')->setLabel('Notif. Email'),
            TextField::new('job_title')->setLabel('Job Title')
        ];

    }
}
