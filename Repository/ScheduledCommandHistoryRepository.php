<?php

namespace Flashmer\CommandSchedulerBundle\Repository;
use Doctrine\ORM\EntityRepository;
use Flashmer\CommandSchedulerBundle\Entity\ScheduledCommandHistory;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<ScheduledCommandHistory>
 *
 * @method ScheduledCommandHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduledCommandHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduledCommandHistory[]    findAll()
 * @method ScheduledCommandHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class ScheduledCommandHistoryRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledCommandHistoryRepository::class);
    }

    public function save(ScheduledCommandHistoryRepository $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ScheduledCommandHistoryRepository $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}