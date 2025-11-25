<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
    #[Route('/', name: 'home_redirect')]
    public function index(): Response
    {
      if ($this->getUser()) {
        return $this->redirectToRoute('app_checkin'); // Remplace 'dashboard_page' par le nom de ta route cible
      }
        return $this->redirectToRoute('app_login'); // ou 'login_page' selon ton nom
    }
}