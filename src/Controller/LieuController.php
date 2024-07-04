<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Contrôleur pour les pages de gestion des lieux
#[Route('/lieu', name: 'lieu_')]
class LieuController extends AbstractController
{

    private EntityManagerInterface $em;
    private LieuRepository $lieuRep;
    private MessageService $messageService;

    public function __construct(
        EntityManagerInterface $em,
        LieuRepository $lieuRep,
        MessageService $messageService
    )
    {
        $this->em = $em;
        $this->lieuRep = $lieuRep;
        $this->messageService = $messageService;
    }

    // Affiche la page de création d'un lieu
    #[Route('/creer', name: 'creer')]
    public function creer( Request $request ): Response
    {

        $lieu = new Lieu();

        // Création du formulaire de création de lieu
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        //Validation du formulaire
        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $existantLieu = $this->lieuRep->findOneBy(['nom' => $lieu->getNom()]);

            // Vérifie si le lieu existe déjà
            if ($existantLieu) {
                return $this->redirectWithMessage(
                    'danger',
                    $this->messageService->get('lieu.dejaexistant'),
                    'lieu_creer');
            }


            $this->em->persist($lieu);
            $this->em->flush();
            return $this->redirectWithMessage(
                'success',
                $this->messageService->get('lieu.ajouter'),
                'sortie_creer');
        }

        return $this->render('lieu/creer.html.twig', [
            'lieuForm' => $lieuForm
        ]);
    }

    // Affiche la page de modification d'un lieu grâce à son id
    #[Route('/lister/{idLieu}', name: 'lister', requirements: ['idLieu' => '\d+'])]
    public function getLieuDetails(int $idLieu): Response
    {
        $lieu = $this->lieuRep->find($idLieu);
        return $this->json($lieu, Response::HTTP_OK);
    }

    /**
     * Redirige vers la page de détails de la sortie avec un message flash.
     *
     * @param string $type Le type de message flash (e.g. 'success', 'error').
     * @param string $message Le contenu du message flash.
     * @param string $chemin Le chemin de la sortie.
     * @return Response
     */
    private function redirectWithMessage($type, $message, $chemin): Response
    {
        $this->addFlash($type, $message);
        return $this->redirectToRoute($chemin);
    }
}