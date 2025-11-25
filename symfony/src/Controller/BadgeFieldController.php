<?php

namespace App\Controller;

use App\Entity\BadgeField;
use App\Form\BadgeFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/badge/field')]
final class BadgeFieldController extends AbstractController
{
    #[Route(name: 'app_badge_field_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $badgeFields = $entityManager
            ->getRepository(BadgeField::class)
            ->findAll();

        return $this->render('badge_field/index.html.twig', [
            'badge_fields' => $badgeFields,
        ]);
    }

    #[Route('/new', name: 'app_badge_field_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $badgeField = new BadgeField();
        $form = $this->createForm(BadgeFieldType::class, $badgeField);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($badgeField);
            $entityManager->flush();

            return $this->redirectToRoute('app_badge_field_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badge_field/new.html.twig', [
            'badge_field' => $badgeField,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_badge_field_show', methods: ['GET'])]
    public function show(BadgeField $badgeField): Response
    {
        return $this->render('badge_field/show.html.twig', [
            'badge_field' => $badgeField,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_badge_field_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BadgeField $badgeField, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BadgeFieldType::class, $badgeField);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_badge_field_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badge_field/edit.html.twig', [
            'badge_field' => $badgeField,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_badge_field_delete', methods: ['POST'])]
    public function delete(Request $request, BadgeField $badgeField, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$badgeField->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($badgeField);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_badge_field_index', [], Response::HTTP_SEE_OTHER);
    }
}
