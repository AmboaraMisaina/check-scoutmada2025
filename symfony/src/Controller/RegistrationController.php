<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/registration')]
final class RegistrationController extends AbstractController
{
    #[Route(name: 'app_registration_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $registrations = $entityManager
            ->getRepository(Registration::class)
            ->findAll();

        return $this->render('registration/index.html.twig', [
            'registrations' => $registrations,
        ]);
    }

    #[Route('/new', name: 'app_registration_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $registration = new Registration();
        $registration->setCreatedAt(new \DateTimeImmutable());
        $registration->setUpdatedAt(new \DateTimeImmutable());
        $registration->setCreatedBy($this->getUser()->getId());

        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registration);
            $entityManager->flush();

            return $this->redirectToRoute('app_registration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration/new.html.twig', [
            'registration' => $registration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registration_show', methods: ['GET'])]
    public function show(Registration $registration): Response
    {
        return $this->render('registration/show.html.twig', [
            'registration' => $registration,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_registration_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Registration $registration, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_registration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration/edit.html.twig', [
            'registration' => $registration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registration_delete', methods: ['POST'])]
    public function delete(Request $request, Registration $registration, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$registration->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($registration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_registration_index', [], Response::HTTP_SEE_OTHER);
    }
}
