<?php

namespace App\Controller;

use App\Entity\RegistrationStep;
use App\Form\RegistrationStepType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/registrationstep')]
final class RegistrationStepController extends AbstractController
{
    #[Route(name: 'app_registration_step_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $registrationSteps = $entityManager
            ->getRepository(RegistrationStep::class)
            ->findAll();

        return $this->render('registration_step/index.html.twig', [
            'registration_steps' => $registrationSteps,
        ]);
    }

    #[Route('/new', name: 'app_registration_step_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $registrationStep = new RegistrationStep();
        $registrationStep->setCreatedAt(new \DateTimeImmutable());
        $registrationStep->setUpdatedAt(new \DateTimeImmutable());
        $registrationStep->setCreatedBy($this->getUser()->getId());
        $registrationStep->setUpdatedBy($this->getUser()->getId());

        $form = $this->createForm(RegistrationStepType::class, $registrationStep);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registrationStep);
            $entityManager->flush();

            return $this->redirectToRoute('app_registration_step_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration_step/new.html.twig', [
            'registration_step' => $registrationStep,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registration_step_show', methods: ['GET'])]
    public function show(RegistrationStep $registrationStep): Response
    {
        return $this->render('registration_step/show.html.twig', [
            'registration_step' => $registrationStep,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_registration_step_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RegistrationStep $registrationStep, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationStepType::class, $registrationStep);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_registration_step_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration_step/edit.html.twig', [
            'registration_step' => $registrationStep,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_registration_step_delete', methods: ['POST'])]
    public function delete(Request $request, RegistrationStep $registrationStep, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$registrationStep->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($registrationStep);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_registration_step_index', [], Response::HTTP_SEE_OTHER);
    }
}
