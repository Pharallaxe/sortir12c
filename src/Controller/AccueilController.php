<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Contrôleur pour les pages d'accueil et de paramétrage de l'accessibilité du site
class AccueilController extends AbstractController
{
    #[Route('/a-propos', name: 'site_description')]
    public function decrire(): Response
    {
        return $this->render('accueil/decrire.html.twig');
    }

    #[Route('/accessibilite', name: 'accessibilite')]
    public function specifierAccessibilite(): Response
    {
        return $this->render('accueil/accessibilite.html.twig');
    }
}
