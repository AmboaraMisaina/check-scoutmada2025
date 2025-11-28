<?php

namespace App\Service;

use App\Entity\EventAccreditation;
use Doctrine\ORM\EntityManagerInterface;

class AccreditationEventService{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    
    public function updateOrInsertStatus(int $idType , int $idEvent, String $status): void
    { 
        $repository = $this->entityManager->getRepository('App\Entity\EventAccreditation');
        $accreditation = $repository->findOneBy([
            'participantType' => $idType,
            'event' => $idEvent
        ]);

        if ($accreditation) {
            if (!$accreditation->getStatus()  == $status){
              $accreditation->setStatus($status);
              $this->entityManager->persist($accreditation);
            }

        } else {
            // Insérer un nouveau statut
            $accreditation = new EventAccreditation();
            $accreditation->setParticipantType($this->entityManager->getReference('App\Entity\ParticipantType', $idType));
            $accreditation->setEvent($this->entityManager->getReference('App\Entity\Event', $idEvent));
            $accreditation->setStatus($status); // Remplacez par le statut souhaité
            $this->entityManager->persist($accreditation);
        }
        $this->entityManager->flush();
    }
}