<?php

namespace App\Service;

use App\Entity\Program;
use App\Entity\Participant;
use App\Entity\ProgramRegistration;
use App\Repository\ProgramRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProgramRegistrationService
{
    private ProgramRegistrationRepository $registrationRepository;
    private EntityManagerInterface $em;

    public function __construct(
        ProgramRegistrationRepository $registrationRepository,
        EntityManagerInterface $em
    ) {
        $this->registrationRepository = $registrationRepository;
        $this->em = $em;
    }

    /**
     * Inscription d’un participant à un programme
     */
    public function registerParticipant(Participant $participant, Program $program, int $createdBy): array
    {
        // Vérifier doublon
        if ($this->registrationRepository->isParticipantAlreadyRegistered($participant, $program)) {
            return [
                'success' => false,
                'message' => 'Ce participant est déjà inscrit à ce programme.'
            ];
        }

        $registration = new ProgramRegistration();
        $registration->setParticipant($participant);
        $registration->setProgram($program);
        $registration->setCreatedBy($createdBy);

        $this->em->persist($registration);
        $this->em->flush();

        return [
            'success' => true,
            'message' => 'Inscription réussie.',
            'registration' => $registration
        ];
    }
}
