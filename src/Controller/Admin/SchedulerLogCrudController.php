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
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Exception;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\SchedulerLog;

#[AllowDynamicProperties]  class SchedulerLogCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly BundleVersionProvider $bundleVersionProvider,
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
            ->setEntityLabelInPlural('Logs')
            ->setHelp('index', 'Bundle version ' . $this->bundleVersionProvider->getBundleVersion());
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
