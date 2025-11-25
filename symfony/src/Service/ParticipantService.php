<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Organization; 
use App\Entity\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\User\UserInterface; // Importation pour l'utilisateur connecté

class ParticipantService{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
}