<?php

namespace App\Service;

use App\Entity\ProgramRegistration;
use Doctrine\ORM\EntityManagerInterface;

class ProgramService{

  private EntityManagerInterface $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
      $this->entityManager = $entityManager;
  }

  public function getParticipantByProgram(int $programId): array
  {
      $repository = $this->entityManager->getRepository(ProgramRegistration::class);
      return $repository->findByProgram($programId);
  }   
}