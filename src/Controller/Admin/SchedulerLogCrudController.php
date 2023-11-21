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

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\SchedulerLog;

class SchedulerLogCrudController extends AbstractCrudController
{
    public function __construct(
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SchedulerLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Log')
            ->setEntityLabelInPlural('Logs');
    }


    /**
     * @throws Exception
     */
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id', 'ID')->hideOnForm()->setSortable(false)->hideOnIndex();
        $date = DateTimeField::new('date')->setFormat('Y-M-d HH:mm:ss');
        $command = TextField::new('command');
        $arguments = TextField::new('arguments');
        $duration = TextField::new('duration');
        $returnCode = TextField::new('return_code');


        # LISTING
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                $id,
                $date,
                $command,
                $arguments,
                $duration,
                $returnCode,
            ];
        }
        return [];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->remove(Crud::PAGE_INDEX, Action::NEW)
        ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }
}
