<?php

namespace App\Controller;

use App\Entity\ParticipantType;
use App\Form\ParticipantTypeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participant-type')]
final class ParticipantTypeController extends AbstractController
{
    #[Route(name: 'app_participant_type_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $participantTypes = $entityManager
            ->getRepository(ParticipantType::class)
            ->findAll();

        return $this->render('participant_type/index.html.twig', [
            'participant_types' => $participantTypes,
        ]);
    }

    #[Route('/new', name: 'app_participant_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participantType = new ParticipantType();
        $now = new \DateTimeImmutable();
        $participantType->setCreatedAt($now);
        $participantType->setUpdatedAt($now);
        $form = $this->createForm(ParticipantTypeType::class, $participantType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participantType);
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant_type/new.html.twig', [
            'participant_type' => $participantType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_type_show', methods: ['GET'])]
    public function show(ParticipantType $participantType): Response
    {
        return $this->render('participant_type/show.html.twig', [
            'participant_type' => $participantType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ParticipantType $participantType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipantTypeType::class, $participantType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant_type/edit.html.twig', [
            'participant_type' => $participantType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_type_delete', methods: ['POST'])]
    public function delete(Request $request, ParticipantType $participantType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participantType->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participantType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
