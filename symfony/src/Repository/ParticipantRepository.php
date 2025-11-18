<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    // Exemple de méthode personnalisée : récupérer tous les participants d'un type spécifique
    public function findByType(int $participantTypeId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.participant_type_id = :typeId')
            ->setParameter('typeId', $participantTypeId)
            ->orderBy('p.last_name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Exemple de méthode personnalisée : chercher par email
    public function findByEmail(string $email): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
