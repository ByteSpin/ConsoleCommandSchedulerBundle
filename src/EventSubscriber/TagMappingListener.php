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

declare(strict_types=1);

namespace ByteSpin\ConsoleCommandSchedulerBundle\EventSubscriber;

use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TagMappingListener
{
    /**
     * @param class-string $tagClass
     */
    public function __construct(
        private readonly string $tagClass,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();

        if (Scheduler::class !== $metadata->getName()) {
            return;
        }

        if ($metadata->hasAssociation('tags')) {
            return;
        }

        $metadata->mapManyToMany([
            'fieldName' => 'tags',
            'targetEntity' => $this->tagClass,
            'joinTable' => [
                'name' => 'scheduler_tag',
                'joinColumns' => [[
                    'name' => 'scheduler_id',
                    'referencedColumnName' => 'id',
                ]],
                'inverseJoinColumns' => [[
                    'name' => 'tag_id',
                    'referencedColumnName' => 'id',
                ]],
            ],
        ]);
    }
}
