<?php

namespace App\Controller;

use App\Entity\Program;
use App\Form\ProgramType;
use App\Repository\ProgramRepository;
use App\Service\ProgramService;
use App\Service\RegistrationStepService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/program')]
final class ProgramController extends AbstractController
{

    #[Route('/{id}/participants', name: 'api_program_participants', methods: ['GET'])]
    public function apiParticipants(Program $program, ProgramService $programService, RegistrationStepService $registrationService): Response
    {
        $registrations = $programService->getParticipantByProgram($program->getId());
        $registrationStep = $registrationService->getRegistrationStepsByOrganization($program->getOrganization());
        
        // Transformer l'objet Participant en tableau JSON
        $data = [];
        foreach ($registrations as $registration) {
            $participant = $registration->getParticipant(); // <-- accéder au participant

            $data['participants'][] = [
                'id' => $participant->getId(),
                'firstName' => $participant->getFirstName(),
                'lastName' => $participant->getLastName(),
                'email' => $participant->getEmail(),
                'country' => $participant->getCountry(),
                'participantType' => $participant->getParticipantType()?->getName(), // safe
                'completedStepIds' => $registrationService->getCompletedStepsId($participant, $program) // 💡 liste des steps complétés
            ];
        }

        $data['registrationSteps'] = [];
        foreach ($registrationStep as $step) {
            $data['registrationSteps'][] = [
                'id' => $step->getId(),
                'step' => $step->getStep(),
                'stepOrder' => $step->getStepOrder(),
            ];
        }

        return $this->json($data);
    }

    
    // ==============================
    // Controller AJAX Load
    // ==============================
    #[Route('/programs/load', name: 'app_programs_load', methods: ['GET'])]
    public function loadPrograms(Request $request, ProgramRepository $repo): JsonResponse
    {
        $offset = max(0, (int)$request->query->get('offset', 0));
        $limit = 5;

        $statusFilter = $request->query->get('status'); // 'upcoming', 'in_progress', 'past'

        $programs = $repo->findPrograms($offset, $limit);

        // Déterminer le statut côté PHP
        $now = new \DateTimeImmutable();
        $filteredPrograms = [];

        foreach ($programs as $program) {
            $start = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i',
                $program->getStartDate()->format('Y-m-d') . ' ' . $program->getStartTime()->format('H:i')
            );
            $end = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i',
                $program->getEndDate()->format('Y-m-d') . ' ' . $program->getEndTime()->format('H:i')
            );

            if ($start > $now) {
                $status = 'upcoming';
            } elseif ($start <= $now && $end >= $now) {
                $status = 'in_progress';
            } else {
                $status = 'past';
            }

            // Filtre
            if ($statusFilter && $status !== $statusFilter) continue;

            // Stocker le statut pour Twig
            $program->status = $status;

            $filteredPrograms[] = $program;
        }

        $count = count($filteredPrograms);
        $hasMore = $count === $limit;

        $html = $this->renderView('program/_program_items.html.twig', [
            'programs' => $filteredPrograms,
            'hasMore' => $hasMore,
        ]);

        return $this->json([
            'html' => $html,
            'count' => $count,
            'hasMore' => $hasMore,
        ]);
    }


    // ==============================
    // Controller Page principale
    // ==============================
    #[Route(name: 'app_program_index', methods: ['GET'])]
    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        $statusFilter = $request->query->get('status'); // récupère le filtre si présent

        // Récupérer tous les programmes triés par date et heure
        $programs = $programRepository->findBy([], ['start_date' => 'desc', 'start_time' => 'desc']);

        if ($statusFilter) {
            $now = new \DateTimeImmutable();
            $programs = array_filter($programs, function($program) use ($statusFilter, $now) {
                $start = \DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i',
                    $program->getStartDate()->format('Y-m-d') . ' ' . $program->getStartTime()->format('H:i')
                );
                $end = \DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i',
                    $program->getEndDate()->format('Y-m-d') . ' ' . $program->getEndTime()->format('H:i')
                );

                if ($start > $now) $status = 'upcoming';
                elseif ($start <= $now && $end >= $now) $status = 'in_progress';
                else $status = 'past';

                return $status === $statusFilter;
            });
        }

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
            'statusFilter' => $statusFilter,
        ]);
    }


    #[Route('/new', name: 'app_program_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $program = new Program();
        $now = new \DateTimeImmutable();
        $program->setCreatedAt($now);
        $program->setUpdatedAt($now);
        $program->setCreatedBy($this->getUser()?->getId());
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($program);
            $entityManager->flush();

            return $this->redirectToRoute('app_program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('program/new.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_program_show', methods: ['GET'])]
    public function show(Program $program , ProgramService $programService ): Response
    {
        return $this->render('program/show.html.twig', [
            'program' => $program,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_program_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $now = new \DateTimeImmutable();
        $program->setUpdatedAt($now);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_program_delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($program);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_program_index', [], Response::HTTP_SEE_OTHER);
    }
}
