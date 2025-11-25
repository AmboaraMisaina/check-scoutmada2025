<?php

namespace App\Controller;

use App\Entity\Program;
use App\Form\ProgramType;
use App\Repository\ProgramRepository;
use App\Service\ProgramService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/program')]
final class ProgramController extends AbstractController
{

    #[Route('/{id}/participants', name: 'api_program_participants', methods: ['GET'])]
    public function apiParticipants(Program $program, ProgramService $programService): Response
    {
        $registrations = $programService->getParticipantByProgram($program->getId());

        // Transformer l'objet Participant en tableau JSON
        $data = [];
        foreach ($registrations as $registration) {
            $participant = $registration->getParticipant(); // <-- accéder au participant

            $data[] = [
                'id' => $participant->getId(),
                'firstName' => $participant->getFirstName(),
                'lastName' => $participant->getLastName(),
                'email' => $participant->getEmail(),
                'country' => $participant->getCountry(),
                'participantType' => $participant->getParticipantType()?->getName(), // safe
            ];
        }

        return $this->json($data);
    }

    

    #[Route(name: 'app_program_index', methods: ['GET'])]
    public function index(ProgramRepository $programRepository , ProgramService $programService ): Response
    {
        return $this->render('program/index.html.twig', [
            'programs' => $programRepository->findAll(),
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
