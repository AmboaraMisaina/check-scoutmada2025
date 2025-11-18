<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        // Dans EventController.php → new() et edit()
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            // Vider les anciennes liaisons
            foreach ($event->getOpenTo() as $link) {
                $entityManager->remove($link);
            }
            $event->getOpenTo()->clear();

            // Ajouter les nouvelles
            $categories = $form->get('allowedCategories')->getData();
            foreach ($categories as $category) {
                $event->addAllowedCategory($category);
            }

            $entityManager->persist($event);
            $entityManager->flush();
        }
        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

       // Dans EventController.php → new() et edit()
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            // Vider les anciennes liaisons
            foreach ($event->getOpenTo() as $link) {
                $entityManager->remove($link);
            }
            $event->getOpenTo()->clear();

            // Ajouter les nouvelles
            $categories = $form->get('allowedCategories')->getData();
            foreach ($categories as $category) {
                $event->addAllowedCategory($category);
            }

            $entityManager->persist($event);
            $entityManager->flush();
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
