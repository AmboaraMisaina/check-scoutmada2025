<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CheckinController extends AbstractController
{
    #[Route('/checkin', name: 'app_checkin')]
    public function index(): Response
    {
        return $this->render('checkin/index.html.twig', [
            'controller_name' => 'CheckinController',
        ]);
    }
}
