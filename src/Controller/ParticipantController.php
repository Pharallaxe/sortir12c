<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function participantProfil(
        int                    $id,
        ParticipantRepository $participantRepository): Response
    {

        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, le profil du participant n'a pas été trouvé !");
        }

        return $this->render('participant/detailler.html.twig', [
            'participant' => $participant,
        ]);
    }

}
