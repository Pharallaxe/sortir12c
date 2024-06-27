<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu', name: 'lieu_')]
class LieuController extends AbstractController
{

    #[Route('/creer', name: 'creer')]
    public function creer(
        Request $request,
        LieuRepository $lieuRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {

        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $existantLieu = $lieuRepository->findOneBy(['nom' => $lieu->getNom()]);

            if ($existantLieu) {
                $this->addFlash('error', 'Un lieu avec ce nom existe déjà.');
                return $this->redirectToRoute('lieu_creer');
            }

            $this->addFlash('success', 'Lieu ajouté avec succès !');

            return $this->redirectToRoute('sortie_creer');
        }

        return $this->render('lieu/creer.html.twig', [
            'lieuForm' => $lieuForm
        ]);
    }


}
