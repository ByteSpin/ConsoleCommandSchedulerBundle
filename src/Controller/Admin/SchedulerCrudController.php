<?php

/**
 * This file is part of the ByteSpin/ConsoleCommandSchedulerBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git.
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\Controller\Admin;

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use ByteSpin\ConsoleCommandSchedulerBundle\Provider\BundleVersionProvider;
use ByteSpin\ConsoleCommandSchedulerBundle\Provider\ConsoleCommandProvider;
use ByteSpin\ConsoleCommandSchedulerBundle\Provider\MessengerQueueProvider;
use ByteSpin\ConsoleCommandSchedulerBundle\Scheduler\ConsoleJobsScheduler;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @extends AbstractCrudController<Scheduler>
 */
class SchedulerCrudController extends AbstractCrudController
{
    private bool $tagsEnabled;
    private ?string $tagCrudController;

    public function __construct(
        private readonly ConsoleCommandProvider $consoleCommandProvider,
        private readonly BundleVersionProvider $bundleVersionProvider,
        private readonly MessengerQueueProvider $messengerQueueProvider,
        private readonly CacheInterface $cache,
        ParameterBagInterface $parameterBag,
    ) {
        $this->tagsEnabled = $parameterBag->get('bytespin_scheduler.tags.enabled');
        $this->tagCrudController = $parameterBag->get('bytespin_scheduler.tags.crud_controller');
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
     * @throws \Exception
     */
    public function configureFields(string $pageName): iterable
    {
        // Faults of the LAST schedule build (empty until the scheduler worker
        // builds once): entries the schedule skipped get a red badge with the
        // reason — new entries cannot be saved invalid anymore (entity-level
        // ValidSchedulerEntry constraint), this surfaces drift cases such as
        // a command removed after being scheduled.
        /** @var array<int, string> $buildFaults */
        $buildFaults = $this->cache->get(ConsoleJobsScheduler::FAULTS_CACHE_KEY, static fn (): array => []);

        $fields = [
            IdField::new('id', 'ID')->hideOnForm()->setSortable(false)->hideOnIndex(),
            TextField::new('id', 'Health')->onlyOnIndex()->setSortable(false)
                ->formatValue(static function ($value) use ($buildFaults): string {
                    $fault = $buildFaults[(int) $value] ?? null;

                    return null === $fault
                        ? '<span class="badge badge-success">ok</span>'
                        : sprintf(
                            '<span class="badge badge-danger" title="%s">invalid</span>',
                            htmlspecialchars($fault, ENT_QUOTES),
                        );
                })
                ->renderAsHtml(),
            ChoiceField::new('command')->setChoices($this->consoleCommandProvider->listConsoleCommands()),
            ChoiceField::new('messenger_queue')->setChoices($this->messengerQueueProvider->listMessengerQueues()),
            TextField::new('arguments'),
        ];

        // Add tags field if tags are enabled
        if ($this->tagsEnabled) {
            $tagsField = AssociationField::new('tags')
                ->setLabel('Tags')
                ->autocomplete()
                ->setFormTypeOption('by_reference', false);

            if ($this->tagCrudController) {
                $tagsField->setCrudController($this->tagCrudController);
            }

            $fields[] = $tagsField;
        }

        $fields = array_merge($fields, [
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
            TextField::new('job_title')->setLabel('Job Title'),
        ]);

        return $fields;
    }
}
