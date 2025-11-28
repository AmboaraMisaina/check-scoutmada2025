<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventAccreditation;
use App\Entity\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccreditationEventController extends AbstractController
{
#[Route('/api/event/{id}/accreditations', name: 'api_event_update_accreditations', methods: ['POST'])]
public function updateAccreditations(Request $request, Event $event, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // $data = [ typeId => 'enabled'|'disabled', ... ]
    foreach ($data as $typeId => $status) {
        $accreditation = $em->getRepository(EventAccreditation::class)
            ->findOneBy(['event' => $event, 'participantType' => $typeId]);

        if (!$accreditation && $status === 'enabled') {
            $accreditation = new EventAccreditation();
            $accreditation->setEvent($event);
            $accreditation->setParticipantType($em->getReference(ParticipantType::class, $typeId));
            $accreditation->setStatus('enabled');
            $accreditation->setCreatedBy($this->getUser()->getId());
            $em->persist($accreditation);
        } elseif ($accreditation) {
            $accreditation->setStatus($status);
        }
    }

    $em->flush();

    return $this->json(['success' => true]);
}
}
