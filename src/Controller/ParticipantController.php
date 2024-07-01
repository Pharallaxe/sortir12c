<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
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

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{

    private $em;
    private $participantRep;
    private $security;
    private $messageService;

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

    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function participantProfil(int $id): Response
    {

        $participant = $this->participantRep->find($id);
        $participantConnecte = $this->security->getUser();

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

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if (!$participantForm->isSubmitted() || !$participantForm->isValid()) {
            return $this->render('participant/modifier.html.twig', [
                'modifierParticipantForm' => $participantForm
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

        $this->addFlash('success', 'Le profil a été modifié avec succès.');
        return $this->redirectToRoute('participant_detailler', ['id' => $id]);

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