<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function participantProfil(
        int                   $id,
        ParticipantRepository $participantRepository,
        Security              $security
    ): Response
    {

        $participant = $participantRepository->find($id);
        $participantConnecte = $security->getUser();

        if (!$participant) {
            throw $this->createNotFoundException("Oups, le profil du participant n'a pas été trouvé !");
        }

        return $this->render('participant/detailler.html.twig', [
            'participant' => $participant,
            'participantConnecte' => $participantConnecte
        ]);
    }

    #[Route('/modifier/{id}', name: 'modifier', requirements: ['id' => '\d+'])]
    public function participantModifierProfil(
        int                         $id,
        ParticipantRepository       $participantRepository,
        EntityManagerInterface      $entityManager,
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security                    $security
    ): Response
    {

        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, le profil du participant n'a pas été trouvé !");
        }

        $participantConnecte = $security->getUser();

        if ($participantConnecte->getId() !== $participant->getId()) {
            throw new AccessDeniedException("Vous ne pouvez modifier que votre propre profil.");
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

            /**
             * @var UploadedFile $file
             **/

            $file = $participantForm->get('imageProfil')->getData();

            if(!empty($file)){
                $newFilename = str_replace(
                        ' ', '',
                        $participant->getPseudo()) . '-' . uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('participants_images_directory'), $newFilename);
                $participant->setImageProfile($newFilename);
            }

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Le profil a été modifié avec succès.');

            return $this->redirectToRoute('participant_detailler', ['id' => $id]);
        }

        return $this->render('participant/modifier.html.twig', [
            'modifierParticipantForm' => $participantForm
        ]);
    }
}