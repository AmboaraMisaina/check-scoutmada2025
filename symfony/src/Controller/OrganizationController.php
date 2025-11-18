<?php

namespace App\Controller;

use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrganizationController extends AbstractController
{
    #[Route('/organizations', name: 'app_organization_list')]
    public function index(OrganizationRepository $organizationRepository): Response
    {
        $organizations = $organizationRepository->findAll();

        return $this->render('organization/index.html.twig', [
            'organizations' => $organizations
        ]);
    }
}
