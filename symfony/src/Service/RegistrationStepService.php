<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Organization; 
use App\Entity\ParticipantType;
use App\Entity\Program;
use App\Entity\RegistrationFollowup;
use App\Entity\RegistrationStep;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\User\UserInterface; // Importation pour l'utilisateur connecté

class RegistrationStepService{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    
    public function getRegistrationStepsByOrganization(Organization $organization): array
    {
        return $this->entityManager
            ->getRepository(RegistrationStep::class)
            ->findBy(['organization' => $organization], ['step_order' => 'ASC']);
    }

    public function updateQrCodes(array $participants): void
    { 
        foreach ($participants as $participant) {
            if (!$participant->getId()) continue;

            $qrContent = $participant->getLastName() . '' .$participant->getFirstName() . '' . $participant->getId(); // ou ton URL préférée

            $participant->setQrCode($qrContent);
            $this->entityManager->persist($participant); // ← indispensable !
        }

        $this->entityManager->flush(); // ← un seul flush ici
    }

    public function getCompletedStepsId(Participant $participant, Program $program): array
    {
        $followups = $this->entityManager
            ->getRepository(RegistrationFollowup::class)
            ->findBy([
                'participant' => $participant,
                'program' => $program,
                'status' => 'completed' // ou 1 selon ton implémentation
            ]);

        $completedStepIds = [];
        foreach ($followups as $followup) {
            $completedStepIds[] = $followup->getStep()->getId();
        }

        return $completedStepIds;  
    }
}