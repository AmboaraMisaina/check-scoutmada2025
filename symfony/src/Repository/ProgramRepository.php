<?php

namespace App\Repository;

use App\Entity\Program;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProgramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Program::class);
    }

    /**
     * Récupère une liste de programmes avec pagination
     *
     * @param int $offset
     * @param int $limit
     * @return Program[]
     */
    public function findPrograms(int $offset = 0, int $limit = 5, ?string $status = null)
    {
        $qb = $this->createQueryBuilder('p')
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->orderBy('p.start_date', 'desc')
                ->addOrderBy('p.start_time', 'desc');

        if ($status) {
            $now = new \DateTimeImmutable();

            if ($status === 'upcoming') {
                $qb->andWhere('p.start_date > :now');
                $qb->andWhere('p.start_time > :nowTime')
                ->setParameter('now', $now->format('Y-m-d'))
                ->setParameter('nowTime', $now->format('H:i'));
            } elseif ($status === 'in_progress') {
                $qb->andWhere('p.start_date <= :now AND p.end_date >= :now');
                $qb->andWhere('p.start_time <= :nowTime AND p.end_time >= :nowTime')
                ->setParameter('now', $now->format('Y-m-d'))
                ->setParameter('nowTime', $now->format('H:i'));
            } elseif ($status === 'past') {
                $qb->andWhere('p.end_date < :now');
                $qb->andWhere('p.end_time < :nowTime')
                ->setParameter('now', $now->format('Y-m-d'))
                ->setParameter('nowTime', $now->format('H:i'));
            }
        }

        return $qb->getQuery()->getResult();
    }
}