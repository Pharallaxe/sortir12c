<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

// Contrôleur pour les pages de gestion des participants
#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{

    private EntityManagerInterface $em;
    private ParticipantRepository $participantRep;
    private Security $security;
    private MessageService $messageService;

    public function __construct(
        EntityManagerInterface $em,
        ParticipantRepository  $participantRep,
        Security               $security,
        MessageService         $messageService
    )
    {
        $this->em = $em;
        $this->participantRep = $participantRep;
        $this->security = $security;
        $this->messageService = $messageService;
    }

    // Affiche la page de détail d'un participant grâce à son identifiant
    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function participantProfil(int $id): Response
    {

        $participant = $this->participantRep->find($id);
        $participantConnecte = $this->security->getUser();

        if (!$participant) {
            throw $this->createNotFoundException($this->messageService->get('participant.nontrouve'));
        }

        return $this->render('participant/detailler.html.twig', [
            'participant' => $participant,
            'participantConnecte' => $participantConnecte
        ]);
    }

    // Affiche la page de modification d'un participant grâce à son identifiant
    #[Route('/modifier/{id}', name: 'modifier', requirements: ['id' => '\d+'])]
    public function participantModifierProfil(
        int                         $id,
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
    ): Response
    {

        $participant = $this->participantRep->find($id);

        if (!$participant) {
            throw $this->createNotFoundException($this->messageService->get('participant.nontrouve'));
        }

        $participantConnecte = $this->security->getUser();

        if ($participantConnecte->getId() !== $participant->getId()) {
            throw new AccessDeniedException($this->messageService->get('participant.mauvaisprofil'));
        }

        // Création du formulaire de modification de participant
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);


        //Validation du formulaire
        if (!$participantForm->isSubmitted() || !$participantForm->isValid()) {
            return $this->render('participant/modifier.html.twig', [
                'modifierParticipantForm' => $participantForm,
                'participant' => $participant
            ]);
        }


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

        if (!empty($file)) {
            $newFilename = str_replace(' ', '', $participant->getPseudo())
                . '-'
                . uniqid()
                . '.'
                . $file->guessExtension();

            $file->move($this->getParameter('participants_images_directory'), $newFilename);
            $participant->setImageProfile($newFilename);
        }

        $this->em->persist($participant);
        $this->em->flush();

        $this->addFlash('success', $this->messageService->get('participant.succes'));
        return $this->redirectToRoute('participant_detailler', ['id' => $id]);

    }



}