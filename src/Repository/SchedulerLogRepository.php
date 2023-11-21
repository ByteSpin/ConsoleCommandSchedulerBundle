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
