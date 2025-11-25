<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventAccreditation;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/event')]
final class EventController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route(name: 'app_event_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager
            ->getRepository(Event::class)
            ->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 1. Récupérer l'administrateur connecté
            /** @var Admin $admin */
            $admin = $this->getUser(); 
            
            // 2. Assurer les relations CreatedBy/UpdatedBy sur l'événement lui-même
            $event->setCreatedBy($admin->getId());
            $event->setUpdatedBy($admin->getId());
            
            // 3. Persister l'événement (pour qu'il ait un ID avant les accréditations)
            $this->entityManager->persist($event);
            
            // 4. LOGIQUE D'ENREGISTREMENT DES ACCRÉDITATIONS (champ non mappé)
            
            $selectedTypes = $form->get('authorizedParticipantTypes')->getData(); // Collection de ParticipantType
            
            foreach ($selectedTypes as $participantType) {
                $accreditation = new EventAccreditation();
                
                // L'événement est celui qui vient d'être créé
                $accreditation->setEvent($event); 
                
                // Le type de participant sélectionné
                $accreditation->setParticipantType($participantType); 
                
                // L'administrateur connecté pour la traçabilité
                $accreditation->setCreatedBy($admin->getId()); 
                
                $this->entityManager->persist($accreditation);
            }
            
            // 5. Exécuter toutes les insertions
            $this->entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
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
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();


        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Mise à jour des champs de suivi
            $event->setUpdatedAt(new \DateTimeImmutable());
            $event->setUpdatedBy($admin);
            
            // Gestion de la mise à jour des accréditations (Types de Participants)
            // 1. Supprimer les anciennes accréditations liées à cet événement
            $oldAccreditations = $entityManager->getRepository(EventAccreditation::class)->findBy(['event' => $event]);
            foreach ($oldAccreditations as $accreditation) {
                $entityManager->remove($accreditation);
            }
            
            // 2. Créer les nouvelles accréditations
            $selectedTypes = $form->get('authorizedParticipantTypes')->getData();
            $now = new \DateTimeImmutable();

            foreach ($selectedTypes as $participantType) {
                $accreditation = new EventAccreditation();
                $accreditation->setEvent($event);
                $accreditation->setParticipantType($participantType);
                $accreditation->setCreatedBy($admin->getId()); 
                $accreditation->setCreatedAt($now); // Recréer la date de création pour le suivi
                $accreditation->setUpdatedAt($now);
                $entityManager->persist($accreditation);
            }

            $entityManager->flush();
            $this->addFlash('success', 'L\'événement a été mis à jour avec succès.');

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
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
