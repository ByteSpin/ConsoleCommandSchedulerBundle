<?php

namespace Flashmer\CommandSchedulerBundle\Repository;

use DateTimeInterface;
use Flashmer\CommandSchedulerBundle\Entity\ScheduledCommandHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduledCommandHistory>
 *
 * @method ScheduledCommandHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduledCommandHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduledCommandHistory[]    findAll()
 * @method ScheduledCommandHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class CommandSchedulerHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandSchedulerHistory::class);
    }

    public function save(CommandSchedulerHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CommandSchedulerHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}