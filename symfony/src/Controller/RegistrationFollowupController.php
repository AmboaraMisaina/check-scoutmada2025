<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Program;
use App\Entity\RegistrationFollowup;
use App\Entity\RegistrationStep;
use App\Form\RegistrationFollowupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/registration-followup')]
final class RegistrationFollowupController extends AbstractController
{

    #[Route('/api/new', name: 'api_registration_followup', methods: ['POST'])]
    public function completeStep(Request $request, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $participantId = $data['participant_id'] ?? null;
        $programId = $data['program_id'] ?? null;
        $stepId = $data['step_id'] ?? null;

        if (!$participantId || !$programId || !$stepId) {
            return $this->json(['success' => false, 'message' => 'Paramètres manquants']);
        }

        $followup = new RegistrationFollowup();
        $followup->setCreatedAt(new \DateTimeImmutable());
        $followup->setCreatedBy($this->getUser()->getId());
        $followup->setParticipant($em->getReference(Participant::class, $participantId));
        $followup->setProgram($em->getReference(Program::class, $programId));
        $followup->setStep($em->getReference(RegistrationStep::class, $stepId));
        $followup->setStatus("completed");

        $em->persist($followup);
        $em->flush();

        return $this->json(['success' => true]);
    }


    #[Route(name: 'app_registration_followup_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $registrationFollowups = $entityManager
            ->getRepository(RegistrationFollowup::class)
            ->findAll();

        return $this->render('registration_followup/index.html.twig', [
            'registration_followups' => $registrationFollowups,
        ]);
    }

    #[Route('/new', name: 'app_registration_followup_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $registrationFollowup = new RegistrationFollowup();
        $registrationFollowup->setCreatedAt(new \DateTimeImmutable());
        $registrationFollowup->setCreatedBy($this->getUser()->getId());
        $form = $this->createForm(RegistrationFollowupType::class, $registrationFollowup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registrationFollowup);
            $entityManager->flush();

            return $this->redirectToRoute('app_registration_followup_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration_followup/new.html.twig', [
            'registration_followup' => $registrationFollowup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registration_followup_show', methods: ['GET'])]
    public function show(RegistrationFollowup $registrationFollowup): Response
    {
        return $this->render('registration_followup/show.html.twig', [
            'registration_followup' => $registrationFollowup,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_registration_followup_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RegistrationFollowup $registrationFollowup, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationFollowupType::class, $registrationFollowup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_registration_followup_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration_followup/edit.html.twig', [
            'registration_followup' => $registrationFollowup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registration_followup_delete', methods: ['POST'])]
    public function delete(Request $request, RegistrationFollowup $registrationFollowup, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$registrationFollowup->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($registrationFollowup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_registration_followup_index', [], Response::HTTP_SEE_OTHER);
    }
}
