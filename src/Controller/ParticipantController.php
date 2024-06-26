<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function participantProfil(
        int                   $id,
        ParticipantRepository $participantRepository
    ): Response
    {

        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, le profil du participant n'a pas été trouvé !");
        }

        return $this->render('participant/detailler.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/modifier/{id}', name: 'modifier', requirements: ['id' => '\d+'])]
    public function participantModifierProfil(
        int                         $id,
        ParticipantRepository       $participantRepository,
        EntityManagerInterface      $entityManager,
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response
    {

        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, le profil du participant n'a pas été trouvé !");
        }

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            $newPassword = $participantForm->get('password')->getData();

            if (!empty($newPassword)) {
                $participant->setPassword(
                    $userPasswordHasher->hashPassword($participant, $newPassword)
                );
            }

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Le profil a été modifié avec succès.');

            return $this->redirectToRoute('participant_detailler', ['id' => $id]);
        }

        return $this->render('participant/modifier.html.twig', [
            'modifierParticipantForm' => $participantForm,
        ]);
    }

}
