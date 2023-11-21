<?php

/**
 * Copyright (c) 2023 Greg LAMY <greg@bytespin.net>
 *
 * This project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git
 *
 * This bundle was originally developed as part of an ETL project.
 *
 * ByteSpin/ConsoleCommandSchedulerBundle is a Symfony 6.3 simple bundle that allows you to schedule console commands easily:
 * - Use the latest messenger/scheduler Symfony 6.3+ components,
 * - Log all console commands data (last execution time, duration, return code) in database and log file,
 * - An admin interface is available with the help of EasyCorp/EasyAdmin bundle
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Form\Type\CommandChoiceType;
use ByteSpin\ConsoleCommandSchedulerBundle\Provider\ConsoleCommandProvider;

class SchedulerCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ConsoleCommandProvider $consoleCommandProvider,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Scheduler::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Console Command')
            ->setEntityLabelInPlural('Console Commands');
    }


    /**
     * @throws Exception
     */
    public function configureFields(string $pageName): iterable
    {

        return [
        IdField::new('id', 'ID')->hideOnForm()->setSortable(false)->hideOnIndex(),
        ChoiceField::new('command')->setChoices($this->consoleCommandProvider->listConsoleCommands()),
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
    ];

    }
}
