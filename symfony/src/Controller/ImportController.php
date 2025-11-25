<?php
// src/Controller/ImportController.php

namespace App\Controller;

use App\Form\ImportType;
use App\Service\DataImporterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportController extends AbstractController
{
    #[Route('/import/{entityName}', name: 'app_import_generic')]
    public function import(
        Request $request, 
        string $entityName, 
        SluggerInterface $slugger, 
        DataImporterService $importerService
    ): Response
    {
        // Nettoyer le nom de l'entité pour l'affichage (ex: 'participant' devient 'Participant')
        $cleanEntityName = ucfirst(strtolower($entityName)); 
        
        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            if ($uploadedFile) {
                // 1. Déplacer le fichier
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                $uploadDir = $this->getParameter('kernel.project_dir') . '/var/imports/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $uploadedFile->move($uploadDir, $newFilename);
                $filePath = $uploadDir . $newFilename;

                // 2. Appel au service d'importation générique
                $user = $this->getUser(); 

                // Appel correct du service
                $results = $importerService->importData($filePath, $cleanEntityName, $user);
                
                // 3. Affichage des résultats
                if (empty($results['errors'])) {
                    $this->addFlash('success', "Importation réussie : {$results['imported']} {$cleanEntityName}s ajoutés.");
                } else {
                    $this->addFlash('warning', "Importation terminée avec {$results['imported']} succès et {$results['errors_count']} erreurs.");
                    // Stocker les détails d'erreurs en session pour affichage
                    $request->getSession()->set('import_errors', $results['errors']);
                }

                return $this->redirectToRoute('app_import_generic', ['entityName' => $entityName]);
            }
        }

        // Récupérer les erreurs de la session pour l'affichage
        $errors = $request->getSession()->get('import_errors', []);
        $request->getSession()->remove('import_errors'); // Nettoyer après lecture

        return $this->render('import/index.html.twig', [
            'form' => $form->createView(),
            'entity_name' => $cleanEntityName,
            'errors' => $errors,
        ]);
    }
}