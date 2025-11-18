<?php

namespace App\Controller;

use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgramController extends AbstractController
{
    #[Route('/programs', name: 'app_program_index')]
    public function index(ProgramRepository $programRepository): Response
    {
        // Récupérer tous les programmes
        $programs = $programRepository->findAll();

        // Envoyer les données au template
        return $this->render('program/index.html.twig', [
            'programs' => $programs,
        ]);
    }
}
