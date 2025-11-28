<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventAccreditation;
use App\Entity\ParticipantType;
use App\Entity\Program;
use App\Repository\EventRepository; 
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


    #[Route('/events', name: 'app_event_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $programRepository = $this->entityManager->getRepository(Program::class);

        // Filtres
        $startDate = $request->query->get('startDate');
        $endDate   = $request->query->get('endDate');
        $programId = $request->query->get('program');  // 👈 ID du programme depuis la liste déroulante

        $qb = $eventRepository->createQueryBuilder('e');

        // Filtre titre
        if (!empty($title)) {
            $qb->andWhere('e.title LIKE :title')
            ->setParameter('title', '%' . $title . '%');
        }

        // Filtre date début
        if (!empty($startDate)) {
            try {
                $qb->andWhere('e.startDate >= :startDate')
                ->setParameter('startDate', new \DateTime($startDate));
            } catch (\Exception $e) {}
        }

        // Filtre date fin
        if (!empty($endDate)) {
            try {
                $qb->andWhere('e.endDate <= :endDate')
                ->setParameter('endDate', new \DateTime($endDate));
            } catch (\Exception $e) {}
        }

        // ✅ Filtre par programme (lié par ID)
        if (!empty($programId)) {
            $qb->andWhere('e.program = :programId')
            ->setParameter('programId', $programId);
        }

        $events = $qb->orderBy('e.startDate', 'ASC')
                    ->getQuery()
                    ->getResult();

        $programs = $programRepository->findAll();

        return $this->render('event/index.html.twig', [
            'events'   => $events,
            'programs' => $programs,
            'filters'  => [
                'startDate' => $startDate,
                'endDate'   => $endDate,
                'program'   => $programId,
            ]
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
                
                $accreditation->setStatus('enabled'); // Statut par défaut
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
public function show(Event $event, EntityManagerInterface $em): Response
{
    // Tous les types de participants
    $participantTypes = $em->getRepository(ParticipantType::class)->findAll();

    // Les accréditations de cet event
    $accreditations = $em->getRepository(EventAccreditation::class)
        ->createQueryBuilder('ea')
        ->select('IDENTITY(ea.participantType) as typeId, ea.status')
        ->where('ea.event = :event')
        ->setParameter('event', $event)
        ->getQuery()
        ->getArrayResult();

    // Transformer en tableau associatif typeId => status
    $accreditedTypeStatuses = [];
    foreach ($accreditations as $accr) {
        $accreditedTypeStatuses[$accr['typeId']] = $accr['status'];
    }

    return $this->render('event/show.html.twig', [
        'event' => $event,
        'participantTypes' => $participantTypes,
        'accreditedTypeStatuses' => $accreditedTypeStatuses,
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
                $accreditation->setStatus('enabled');
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
