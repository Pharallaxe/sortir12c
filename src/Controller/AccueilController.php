<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'site_accueil')]
    public function accueillir(): Response
    {
        return $this->render('accueil/accueil.html.twig');
    }

    #[Route('/accessibilite', name: 'accessibilite')]
    public function specifierAccessibilite(): Response
    {
        return $this->render('accueil/accessibilite.html.twig');
    }
}
