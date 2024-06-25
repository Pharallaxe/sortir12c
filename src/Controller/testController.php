<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class testController extends AbstractController
{

    #[Route('/test', name: 'site_accueil')]
    public function index(): Response
    {
        return $this->render('test/index.html.twig');
    }
}
