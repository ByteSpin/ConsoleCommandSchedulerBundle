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
