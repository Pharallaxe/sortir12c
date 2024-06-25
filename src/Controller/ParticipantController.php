<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/{id}', name: 'profil', requirements: ['id' => '\d+'])]
    public function participantProfil(
        int                    $id,
        ParticipantRepository $participantRepository): Response
    {

        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, le participant n'a pas été trouvé !");
        }

        return $this->render('participant/profil.html.twig', [
            'participant' => $participant,
        ]);
    }
}
