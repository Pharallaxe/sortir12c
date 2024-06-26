<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/sorties/detailler/{id}', name: 'sortie_detailler', requirements: ['id' => '\d+'])]
    public function detail(
        int                    $id,
        SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('sortie/detailler.html.twig', [
            'sortie' => $sortie,
        ]);
    }


    #[Route('/sorties/creer', name: 'sortie_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response
    {
        $sortie = new Sortie();

//        $campusParDefaut = "Rennes";
//        if ($this->getUser()) {
//            $organisateur = $this->getUser();
//            $campusParDefaut = $organisateur->getCampus()->getNom();
//        }


        $form = $this->createForm(SortieType::class, $sortie,
//            [
//            'campus_par_defaut' => $campusParDefaut,
//        ]
        );

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée avec succès !');
            return $this->redirectToRoute('sortie_detailler', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/creer.html.twig', [
            'sortieForm' => $sortieForm
        ]);

    }

}
