<?php

namespace App\Controller;

use App\Entity\BadgeField;
use App\Entity\BadgeTemplate;
use App\Form\BadgeTemplateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/badge')]
final class BadgeTemplateController extends AbstractController
{

    #[Route('/{id}/save-fields', name: 'app_badge_template_save_fields', methods: ['POST'])]
public function saveFields(BadgeTemplate $badgeTemplate, Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $content = json_decode($request->getContent(), true);
    $fieldsData = $content['fields'] ?? [];

    if (empty($fieldsData)) {
        return $this->json(['success' => false, 'message' => 'Aucune donnée de champ reçue.'], 400);
    }

    // 1. Suppression des anciens champs (pour simplifier la mise à jour)
    $existingFields = $entityManager->getRepository(BadgeField::class)->findBy(['badgeTemplate' => $badgeTemplate]);
    foreach ($existingFields as $field) {
        $entityManager->remove($field);
    }
    $entityManager->flush();
    
    // 2. Création des nouveaux champs
    foreach ($fieldsData as $data) {
        $field = new BadgeField();
        $field->setBadgeTemplate($badgeTemplate);
        
        // Mapping des données reçues (pos_x, pos_y, etc.)
        $field->setFieldName($data['field_name']);
        // 🚨 Conversion : Si vous travaillez en mm ou points, convertissez ici les pixels
        $field->setPosX((int) $data['pos_x']); 
        $field->setPosY((int) $data['pos_y']);
        $field->setWidth((int) $data['width']);
        $field->setHeight((int) $data['height']);
        
        // Définir la couleur et taille de police par défaut ou à partir des données si elles sont envoyées
        // $field->setFontSize(12); 
        // $field->setFontColor('#000000'); 

        $entityManager->persist($field);
    }

    $entityManager->flush();

    return $this->json(['success' => true, 'message' => 'Champs de badge enregistrés.']);
}

    #[Route('/{id}/design', name: 'app_badge_template_design', methods: ['GET', 'POST'])]
    public function design(BadgeTemplate $badgeTemplate, EntityManagerInterface $entityManager): Response
    {
        // Charger tous les champs existants pour ce modèle
        $existingFields = $entityManager->getRepository(BadgeField::class)->findBy(['badgeTemplate' => $badgeTemplate]);

        // Définir la liste des champs disponibles à mapper depuis l'entité Participant
        $availableParticipantFields = [
            'first_name' => 'Prénom',
            'last_name' => 'Nom',
            'email' => 'Email',
            'country' => 'Pays',
            'participant_type' => 'Catégorie Participant',
            'qr_code' => 'Code QR', // Pour placer l'image du QR
            // Ajoutez tout autre champ pertinent
        ];

        return $this->render('badge_template/design.html.twig', [
            'badge_template' => $badgeTemplate,
            'existing_fields' => $existingFields,
            'available_fields' => $availableParticipantFields,
        ]);
    }
    #[Route(name: 'app_badge_template_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $badgeTemplates = $entityManager
            ->getRepository(BadgeTemplate::class)
            ->findAll();

        return $this->render('badge_template/index.html.twig', [
            'badge_templates' => $badgeTemplates,
        ]);
    }

    #[Route('/new', name: 'app_badge_template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $badgeTemplate = new BadgeTemplate();
        $form = $this->createForm(BadgeTemplateType::class, $badgeTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 🚩 1. RÉCUPÉRATION DU FICHIER TÉLÉVERSÉ
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            if ($uploadedFile) {
                // Définir le répertoire de destination
                $destinationDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/badge_templates';
                
                // Créer un nom de fichier sûr (slug)
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                try {
                    // Déplacer le fichier vers le répertoire cible
                    $uploadedFile->move(
                        $destinationDirectory,
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'erreur d'upload (permission, etc.)
                    $this->addFlash('error', "Erreur lors du téléversement du fichier : " . $e->getMessage());
                    return $this->redirectToRoute('app_badge_template_new');
                }

                // 🚩 2. METTRE À JOUR LE CHEMIN DANS L'ENTITÉ
                // Nous stockons le chemin public relatif
                $badgeTemplate->setFilePath('uploads/badge_templates/' . $newFilename);
            }
            
            // 3. Gestion des dates (puisque vous les avez enlevées du formulaire)
            $now = new \DateTimeImmutable();
            if (!$badgeTemplate->getCreatedAt()) {
                $badgeTemplate->setCreatedAt($now);
            }
            $badgeTemplate->setUpdatedAt($now);

            // 4. Persistance
            $entityManager->persist($badgeTemplate);
            $entityManager->flush();

            $this->addFlash('success', 'Modèle de badge créé avec succès.');
            return $this->redirectToRoute('app_badge_template_index'); // Assurez-vous d'avoir cette route
        }

        return $this->render('badge_template/new.html.twig', [
            'badge_template' => $badgeTemplate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_badge_template_show', methods: ['GET'])]
    public function show(BadgeTemplate $badgeTemplate): Response
    {
        return $this->render('badge_template/show.html.twig', [
            'badge_template' => $badgeTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_badge_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BadgeTemplate $badgeTemplate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BadgeTemplateType::class, $badgeTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_badge_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badge_template/edit.html.twig', [
            'badge_template' => $badgeTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_badge_template_delete', methods: ['POST'])]
    public function delete(Request $request, BadgeTemplate $badgeTemplate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$badgeTemplate->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($badgeTemplate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_badge_template_index', [], Response::HTTP_SEE_OTHER);
    }
}
