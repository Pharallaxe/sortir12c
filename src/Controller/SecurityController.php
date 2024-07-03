<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Contrôleur pour les pages de connexion et de déconnexion
class SecurityController extends AbstractController
{
    // Affiche la page de connexion
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    // Redirige vers la page de connexion
    #[Route('/', name: 'app_login_redirect')]
    public function redirectToLogin(SortieRepository $sortieRepository): Response
    {

        return $this->redirectToRoute('app_login');
    }

    // Route permettant de surcharger la déconnexion
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {

    }
}