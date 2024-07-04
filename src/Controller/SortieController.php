<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\SortieRecherche;
use App\Form\SortieRechercheType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Service\HistoService;
use App\Service\MessageService;
use App\Service\ClotureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Contrôleur pour les pages de gestion des sorties
#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

    private EntityManagerInterface $em;
    private SortieRepository $sortieRep;
    private CampusRepository $campusRep;
    private EtatRepository $etatRep;
    private LieuRepository $lieuRep;
    private MessageService $messageService;

    // Mise en place des dépendances
    public function __construct(
        EntityManagerInterface $em,
        SortieRepository       $sortieRep,
        CampusRepository       $campusRep,
        EtatRepository         $etatRep,
        LieuRepository         $lieuRep,
        MessageService         $messageService
    )
    {
        $this->em = $em;
        $this->sortieRep = $sortieRep;
        $this->campusRep = $campusRep;
        $this->etatRep = $etatRep;
        $this->lieuRep = $lieuRep;
        $this->messageService = $messageService;
    }

    // Affiche la page de liste des sorties
    #[Route('/lister', name: 'lister')]
    public function index(Request $request, HistoService $histoService, ClotureService $clotureService): Response
    {
        // Met à jour les sorties passées et les sorties cloturées
        $histoService->update();
        $clotureService->update();

        // Récupère les états et les campus
        $etats = $this->etatRep->findAll();
        $campuses = $this->campusRep->findAll();

        $data = new SortieRecherche();
        $form = $this->createForm(SortieRechercheType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sorties = $this->sortieRep->rechercheParCritere($data, $this->getUser());
        } else {
            $sorties = $this->sortieRep->findAllWithRelations();
        }

        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
            'etats' => $etats,
            'campuses' => $campuses,
            'form' => $form
        ]);
    }

    // Affiche la page de détail d'une sortie grâce à son id
    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function detailler(int $id): Response
    {
        $sortie = $this->sortieRep->find($id);
        return $this->render('sortie/detailler.html.twig', ['sortie' => $sortie]);
    }

    // Affiche la page de création d'une sortie
    #[Route('/creer', name: 'creer')]
    public function creer(Request $request): Response
    {
        $sortie = new Sortie();

        if ($this->getUser() !== null) {
            $sortie->setCampus($this->getUser()->getCampus());
        }
        $message = $this->messageService->get('creer');
        return $this->creerOuModifierForm($request, $sortie, $message, true,);
    }

    // Affiche la page de modification d'une sortie grâce à son id
    #[Route('/modifier/{id}', name: 'modifier')]
    public function modifier(int $id, Request $request): Response
    {
        $sortie = $this->sortieRep->find($id);
        $message = $this->messageService->get('modifier');
        return $this->creerOuModifierForm($request, $sortie, $message, false,);
    }

    // Crée ou modifie une sortie en fonction de la requête
    private function creerOuModifierForm($request, $sortie, $message, $creation): Response
    {
        $premierLieu = $this->lieuRep->trouverPremierLieuParOrdreAlphabetique();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        $pageHtml = $creation ? "creer" : "modifier";

        if (!$sortieForm->isSubmitted() || !$sortieForm->isValid()) {
            return $this->render("sortie/$pageHtml.html.twig", [
                'sortieForm' => $sortieForm,
                'premierLieu' => $premierLieu
            ]);
        }


        // Comparaison des dates
        $isDateDebutBeforeOrEqualToDateFin = $sortie->getDateHeureDebut() < $sortie->getDateLimiteInscription();
        if ($isDateDebutBeforeOrEqualToDateFin) {
            return $this->redirectWithMessage('danger', $this->messageService->get('date'), $sortie->getId());
        }


        $sortie->setOrganisateur($this->getUser());
        $sortie->setEtat($this->etatRep->findOneBy(['libelle' => 'Créée']));
        if ($creation) {
            $sortie->addParticipant($this->getUser());
        }
        $this->em->persist($sortie);
        $this->em->flush();

        return $this->redirectWithMessage('success', $message, $sortie->getId());
    }

    // Affiche la page d'annulation d'une sortie grâce à son id
    #[Route('/annuler/{id}', name: 'annuler', requirements: ['id' => '\d+'])]
    public function annuler(int $id): Response
    {
        // Récupère la sortie et le repository des états
        $etatRep = $this->em->getRepository(Etat::class);
        $sortie = $this->sortieRep->find($id);


        // Vérifie si la sortie est bien créée par l'utilisateur connecté
        if ($sortie->getOrganisateur() !== $this->getUser()) {
            return $this->redirectWithMessage(
                'danger',
                $this->messageService->get('annuler.impossible'),
                $id);
        }

        // Passe l'état de la sortie à "Annulée"
        $sortie->setEtat($etatRep->findOneBy(['libelle' => 'Annulée']));

        // Ajoute un message d'annulation au début de la description de la sortie
        $sortie->setInfosSortie("ANNULEE PAR L'ORGANISATEUR " . $sortie->getInfosSortie());
        $this->em->flush();
        return $this->redirectWithMessage(
            'success',
            $this->messageService->get('annuler.succes'),
            $id);
    }

    // Affiche la page de publication d'une sortie grâce à son id
    #[Route('/publier/{id}', name: 'publier', requirements: ['id' => '\d+'])]
    public function publier(int $id): Response
    {
        // Récupère la sortie et le repository des états
        $etatRep = $this->em->getRepository(Etat::class);
        $sortie = $this->sortieRep->find($id);

        // Vérifie si la sortie est bien créée par l'utilisateur connecté
        if ($sortie->getOrganisateur() !== $this->getUser()) {
            return $this->redirectWithMessage(
                'danger', $this->messageService->get('publier.impossible'), $id);
        }

        // Passe l'état de la sortie à "Ouverte"
        $sortie->setEtat($etatRep->findOneBy(['libelle' => 'Ouverte']));
        $this->em->flush();
        return $this->redirectWithMessage('success', $this->messageService->get('publier.succes'), $id);
    }

    // Affiche la page d'inscription d'une sortie grâce à son id
    #[Route('/inscrire/{id}', name: 'inscrire', requirements: ['id' => '\d+'])]
    public function inscrire(int $id): Response
    {
        // Récupère la sortie et le participant connecté
        $sortie = $this->sortieRep->find($id);
        $participant = $this->getUser();

        // Vérifie si le participant est déjà inscrit à la sortie
        if ($sortie->getParticipants()->contains($participant)) {
            return $this->redirectWithMessage(
                'warning', $this->messageService->get('inscrire.dejainscrit'), $id);
        }

        // Vérifie si la sortie est ouverte
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return $this->redirectWithMessage(
                'danger', $this->messageService->get('inscrire.nonouverte'), $id);
        }

        // Vérifie si la date limite d'inscription est dépassée
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            return $this->redirectWithMessage(
                'danger', $this->messageService->get('inscrire.depassee'), $id);
        }

        // Vérifie si le nombre de participants est atteint
        if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()) {
            return $this->redirectWithMessage(
                'danger', $this->messageService->get('inscrire.complete'), $id);
        }

        // Ajoute le participant à la sortie
        $sortie->addParticipant($participant);
        $this->em->flush();
        return $this->redirectWithMessage(
            'success', $this->messageService->get('inscrire.reussie'), $id);
    }

    // Affiche la page de désinscription d'une sortie grâce à son id
    #[Route('/desister/{id}', name: 'desister', requirements: ['id' => '\d+'])]
    public function desister(int $id): Response
    {
        // Récupère la sortie et le participant connecté
        $sortie = $this->sortieRep->find($id);
        $participant = $this->getUser();

        // Vérifie si le participant est inscrit à la sortie
        if (!$sortie->getParticipants()->contains($participant)) {
            return $this->redirectWithMessage(
                'warning', $this->messageService->get('desister.noninscrit'), $id);
        }

        // Vérifie si la sortie est ouverte
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return $this->redirectWithMessage(
                'danger', $this->messageService->get('desister.impossible'), $id);
        }

        // Supprime le participant de la sortie
        $sortie->removeParticipant($participant);
        $this->em->flush();
        return $this->redirectWithMessage(
            'success', $this->messageService->get('desister.succes'), $id);
    }

    /**
     * Redirige vers la page de détails de la sortie avec un message flash.
     *
     * @param string $type Le type de message flash (e.g. 'success', 'error').
     * @param string $message Le contenu du message flash.
     * @param int $id L'identifiant de la sortie.
     * @return Response
     */
    private function redirectWithMessage($type, $message, $id): Response
    {
        $this->addFlash($type, $message);
        return $this->redirectToRoute('sortie_detailler', ['id' => $id]);
    }
}