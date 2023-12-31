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

namespace ByteSpin\ConsoleCommandSchedulerBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;

class SchedulerEasyAdminEventSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->transformDateTimeToString($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->transformDateTimeToString($args);
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Scheduler) {
            return;
        }

        if (is_string($entity->getExecutionFromDate())) {
            $date = \DateTime::createFromFormat('Y-m-d', $entity->getExecutionFromDate());
            if ($date) {
                $entity->setExecutionFromDate($date);
            }
        }

        if (is_string($entity->getExecutionFromTime())) {
            $time = \DateTime::createFromFormat('H:i', $entity->getExecutionFromTime());
            if ($time) {
                $entity->setExecutionFromTime($time);
            }
        }

        if (is_string($entity->getExecutionUntilDate())) {
            $date = \DateTime::createFromFormat('Y-m-d', $entity->getExecutionuntilDate());
            if ($date) {
                $entity->setExecutionuntilDate($date);
            }
        }

        if (is_string($entity->getExecutionuntilTime())) {
            $time = \DateTime::createFromFormat('H:i', $entity->getExecutionUntilTime());
            if ($time) {
                $entity->setExecutionUntilTime($time);
            }
        }
    }

    private function transformDateTimeToString(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Scheduler) {
            return;
        }

        if ($entity->getExecutionFromTime() instanceof \DateTimeInterface) {
            $entity->setExecutionFromTime($entity->getExecutionFromTime()->format('H:i'));
        }
        if ($entity->getExecutionFromDate() instanceof \DateTimeInterface) {
            $entity->setExecutionFromDate($entity->getExecutionFromDate()->format('Y-m-d'));
        }
        if ($entity->getExecutionUntilTime() instanceof \DateTimeInterface) {
            $entity->setExecutionUntilTime($entity->getExecutionUntilTime()->format('H:i'));
        }
        if ($entity->getExecutionUntilDate() instanceof \DateTimeInterface) {
            $entity->setExecutionUntilDate($entity->getExecutionUntilDate()->format('Y-m-d'));
        }
    }
}
