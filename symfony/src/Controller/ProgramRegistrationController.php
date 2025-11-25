<?php

namespace App\Controller;

use App\Entity\Program;
use App\Entity\Participant;
use App\Repository\ProgramRepository;
use App\Repository\ParticipantRepository;
use App\Service\ProgramRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgramRegistrationController extends AbstractController
{
    #[Route('/programs/{id}/register', name: 'program_register', methods: ['POST'])]
    public function register(
        int $id,
        Request $request,
        ProgramRepository $programRepo,
        ParticipantRepository $participantRepo,
        ProgramRegistrationService $registrationService
    ): Response {

        $program = $programRepo->find($id);
        if (!$program) {
            $this->addFlash('danger', 'Programme introuvable.');
            return $this->redirectToRoute('program_list');
        }

        $participantId = $request->request->get('participant_id');
        if (!$participantId) {
            $this->addFlash('danger', 'Aucun participant sélectionné.');
            return $this->redirectToRoute('program_list');
        }

        $participant = $participantRepo->find($participantId);
        if (!$participant) {
            $this->addFlash('danger', 'Participant introuvable.');
            return $this->redirectToRoute('program_list');
        }

        // Exemple : ID de l'utilisateur connecté
        $createdBy = $this->getUser() ? $this->getUser()->getId() : 0;

        $result = $registrationService->registerParticipant($participant, $program, $createdBy);

        if (!$result['success']) {
            $this->addFlash('warning', $result['message']);
        } else {
            $this->addFlash('success', 'Participant inscrit avec succès.');
        }

        return $this->redirectToRoute('program_show', ['id' => $program->getId()]);
    }
}
