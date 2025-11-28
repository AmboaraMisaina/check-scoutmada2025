<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Récupère les événements filtrés par titre et dates.
     *
     * @param string|null $title
     * @param \DateTimeInterface|null $startDate
     * @param \DateTimeInterface|null $endDate
     * @return Event[]
     */
    public function findFiltered(?string $title, ?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($title) {
            $qb->andWhere('e.title LIKE :title')
               ->setParameter('title', '%'.$title.'%');
        }

        if ($startDate) {
            $qb->andWhere('e.startDate >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('e.endDate <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        return $qb
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
