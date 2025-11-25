<?php

namespace App\Repository;

use App\Entity\ProgramRegistration;
use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProgramRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgramRegistration::class);
    }

    /**
     * Retourne la liste des participants inscrits à un programme
     */
    public function findByProgram(int $programId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.participant', 'p')
            ->addSelect('p')
            ->where('r.program = :programId')
            ->setParameter('programId', $programId)
            ->orderBy('p.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
