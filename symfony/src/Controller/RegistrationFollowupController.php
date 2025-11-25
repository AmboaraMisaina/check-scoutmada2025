<?php

namespace App\Controller;

use App\Entity\RegistrationFollowup;
use App\Form\RegistrationFollowupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/registration-followup')]
final class RegistrationFollowupController extends AbstractController
{
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
