<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties/lister', name: 'sortie_lister')]
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();

        // return $this->render('sortie/index.html.twig');
        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    #[Route('/sorties/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function detail(
        int                    $id,
        SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('sortie/detailler.html.twig', [
            'sortie' => $sortie,
        ]);
    }

}