<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Organization; 
use App\Entity\ProgramRegistration; 
use App\Entity\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\User\UserInterface; // Importation pour l'utilisateur connecté

class DataImporterService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private ParticipantService $participantService;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, ParticipantService $participantService)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->participantService = $participantService;
        // La librairie PropertyAccess n'est pas strictement nécessaire ici, on l'enlève pour simplifier
    }

    /**
     * Méthode générique qui route l'appel vers la méthode spécifique.
     * 🚩 Nécessite maintenant l'objet utilisateur connecté ($user).
     */
    public function importData(string $filePath, string $entityName, UserInterface $user): array
    {
        $methodName = 'import' . $entityName; 

        if (!method_exists($this, $methodName)) {
            return [
                'imported' => 0,
                'errors_count' => 1,
                'errors' => [['line' => 0, 'message' => "Type d'entité '{$entityName}' non supporté. Méthode manquante: {$methodName}"]],
            ];
        }

        // Délègue la lecture et la persistance à la méthode spécifique
        return $this->$methodName($filePath, $user); 
    }


    private function importParticipantWithRegistration(string $filePath, UserInterface $user, ?int $programId): array
    {
        if (!$programId) {
            return [
                'imported' => 0,
                'errors_count' => 1,
                'errors' => [['line' => 0, 'message' => 'L\'ID du programme est requis pour cet import.']],
            ];
        }

        $results = ['imported' => 0, 'errors_count' => 0, 'errors' => []];
        $batchSize = 200;
        $rowNumber = 1;

        // Récupérer le programme une seule fois
        $program = $this->entityManager->getRepository(\App\Entity\Program::class)->find($programId);
        if (!$program) {
            return [
                'imported' => 0,
                'errors_count' => 1,
                'errors' => [['line' => 0, 'message' => "Programme avec ID {$programId} introuvable."]],
            ];
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            foreach ($sheet->getRowIterator() as $row) {
                if ($rowNumber++ === 1) continue; // skip header

                $cells = $row->getCellIterator();
                $cells->setIterateOnlyExistingCells(false);
                $data = [];
                foreach ($cells as $cell) {
                    $data[] = trim($cell->getCalculatedValue() ?? '');
                }

                if (empty(array_filter($data))) continue;

                // Colonnes : 0=last_name, 1=first_name, 2=email, 3=country, 4=participant_type_name
                $lastName = $data[0] ?? '';
                $firstName = $data[1] ?? '';
                $email = strtolower($data[2] ?? '');
                $country = $data[3] ?? '';
                $typeName = $data[4] ?? '';

                if (!$email || !$lastName || !$firstName) {
                    $results['errors'][] = ['line' => $rowNumber - 1, 'message' => 'Nom, prénom ou email manquant'];
                    $results['errors_count']++;
                    continue;
                }

                // 1. Trouver ou créer le type de participant
                $type = $this->entityManager->getRepository(ParticipantType::class)->findOneBy(['name' => $typeName]);
                if (!$type) {
                    $results['errors'][] = ['line' => $rowNumber - 1, 'message' => "Type '{$typeName}' introuvable"];
                    $results['errors_count']++;
                    continue;
                }   

                // 2. Chercher si le participant existe déjà (par email = unique)
                $participant = $this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $email]);

                $isNewParticipant = false;
                if (!$participant) {
                    $participant = new Participant();
                    $participant->setLastName($lastName);
                    $participant->setFirstName($firstName);
                    $participant->setEmail($email);
                    $participant->setCountry($country);
                    $participant->setParticipantType($type);
                    $participant->setCreatedBy($user->getId());
                    $participant->setCreatedAt(new \DateTimeImmutable());
                    $isNewParticipant = true;
                }

                $participant->setUpdatedAt(new \DateTimeImmutable());

                // Validation
                $violations = $this->validator->validate($participant);
                if (count($violations) > 0) {
                    $errors = [];
                    foreach ($violations as $violation) $errors[] = $violation->getMessage();
                    $results['errors'][] = ['line' => $rowNumber - 1, 'message' => implode('; ', $errors)];
                    $results['errors_count']++;
                    continue;
                }

                $this->entityManager->persist($participant);

                // 3. Créer l'inscription au programme (si pas déjà inscrit)
                $existingReg = $this->entityManager->getRepository(ProgramRegistration::class)->findOneBy([
                    'participant' => $participant,
                    'program' => $program
                ]);

                if (!$existingReg) {
                    $registration = new ProgramRegistration();
                    $registration->setParticipant($participant);
                    $registration->setProgram($program);
                    $registration->setCreatedBy($user->getId());

                    $this->entityManager->persist($registration);
                    $results['imported']++;
                }

                // Batch flush
                if (($results['imported'] + $results['errors_count']) % $batchSize === 0) {
                    $this->entityManager->flush();
                    if ($isNewParticipant) {
                        $this->participantService->updateQrCodes([$participant]); // ← Génération QR ici
                    }
                    $this->entityManager->clear();
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

        } catch (\Exception $e) {
            $results['errors'][] = ['line' => 0, 'message' => 'Erreur critique : ' . $e->getMessage()];
            $results['errors_count']++;
        }

        return $results;
    }
    /**
     * Implémentation Spécifique pour l'entité Participant.
     * Ordre des colonnes attendu : 0: last_name, 1: first_name, 2: email, 3: country, 4: participant_type_name
     */
    
    private function importParticipant(string $filePath, UserInterface $user): array
    {
        $results = ['imported' => 0, 'errors_count' => 0, 'errors' => []];
        $batchSize = 500;
        $rowNumber = 1;
        $participantsToUpdate = []; // On stocke ceux à mettre à jour après flush

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            foreach ($sheet->getRowIterator() as $row) {
                if ($rowNumber++ === 1) continue; // skip header

                $data = [];
                foreach ($row->getCellIterator() as $cell) {
                    $data[] = $cell->getCalculatedValue();
                }

                if (empty(array_filter($data))) continue;

                // ... ton code existant pour récupérer les données ...

                $participantType = $this->entityManager->getRepository(ParticipantType::class)
                    ->findOneBy(['name' => trim($data[4] ?? '')]);

                if (!$participantType) {
                    $results['errors'][] = ['line' => $rowNumber - 1, 'message' => "Type de participant introuvable."];
                    $results['errors_count']++;
                    continue;
                }

                $participant = new Participant();
                $now = new \DateTimeImmutable();

                $participant->setCreatedBy($user->getId());
                $participant->setCreatedAt($now);
                $participant->setUpdatedAt($now);
                $participant->setLastName(trim($data[0] ?? ''));
                $participant->setFirstName(trim($data[1] ?? ''));
                $participant->setEmail(trim($data[2] ?? ''));
                $participant->setCountry(trim($data[3] ?? ''));
                $participant->setParticipantType($participantType);

                // ⚠️ On ne touche PAS au QR code ici → ID inconnu

                $violations = $this->validator->validate($participant);
                if (count($violations) > 0) {
                    $errors = [];
                    foreach ($violations as $violation) $errors[] = $violation->getMessage();
                    $results['errors'][] = ['line' => $rowNumber - 1, 'message' => implode('; ', $errors)];
                    $results['errors_count']++;
                    continue;
                }

                $this->entityManager->persist($participant);
                $participantsToUpdate[] = $participant; // On garde en mémoire
                $results['imported']++;

                // Flush par batch
                if (($results['imported'] % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->participantService->updateQrCodes($participantsToUpdate); // ← Génération QR ici
                    $participantsToUpdate = [];
                    $this->entityManager->clear();
                }
            }

            // Dernier batch
            if (!empty($participantsToUpdate)) {
                $this->entityManager->flush();
                $this->participantService->updateQrCodes($participantsToUpdate);
                $this->entityManager->clear();
            }

        } catch (\Exception $e) {
            $results['errors'][] = ['line' => 0, 'message' => "Erreur critique: " . $e->getMessage()];
            $results['errors_count']++;
        }

        return $results;
    }

    
    /**
     * Implémentation Spécifique pour l'entité Organization (À compléter).
     */
    private function importOrganization(string $filePath, UserInterface $user): array
    {
        // Cette méthode doit être implémentée si vous utilisez /admin/import/Organization
        return ['imported' => 0, 'errors_count' => 0, 'errors' => [['line' => 0, 'message' => "La méthode d'importation pour Organization n'est pas encore implémentée."]]];
    }
}