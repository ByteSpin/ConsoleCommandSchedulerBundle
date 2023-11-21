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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\Scheduler;

/**
 * @extends ServiceEntityRepository<Scheduler>
 *
 * @method Scheduler|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scheduler|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scheduler[]    findAll()
 * @method Scheduler[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchedulerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scheduler::class);
    }
}
