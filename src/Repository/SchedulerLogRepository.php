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
use ByteSpin\ConsoleCommandSchedulerBundle\Entity\SchedulerLog;

/**
 * @extends ServiceEntityRepository<SchedulerLog>
 *
 * @method SchedulerLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchedulerLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchedulerLog[]    findAll()
 * @method SchedulerLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchedulerLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SchedulerLog::class);
    }
}
