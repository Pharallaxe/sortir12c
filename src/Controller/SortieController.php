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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

    const MESSAGE_AJOUT_SUCCESS = 'Sortie ajoutée avec succès !' ;
    const MESSAGE_MODIFICATION_SUCCESS = 'Sortie modifiée avec succès !' ;

    // MESSAGE DE DESISTEMENT
    const MESSAGE_DESISTEMENT_NONINSCRIT = 'Vous n\'êtes pas inscrit à cette sortie.';
    const MESSAGE_DESISTEMENT_IMPOSSIBLE = 'Vous ne pouvez pas vous désister d\'une sortie qui n\'est pas ouverte.';
    const MESSAGE_DESISTEMENT_SUCCESS = 'Désistement de la sortie réussi !';

    // MESSAGE DE PUBLICATION
    const MESSAGE_PUBLICATION_SUCCESS = 'Sortie publiée avec succès !';
    const MESSAGE_PUBLICATION_IMPOSSIBLE = 'Vous ne pouvez pas publier une sortie que vous n\'avez pas créée.';

    // MESSAGE D'ANNULATION
    const MESSAGE_ANNULATION_SUCCES = 'Sortie annulée avec succès !';
    const MESSAGE_ANNULATION_IMPOSSIBLE = 'Vous ne pouvez pas annuler une sortie que vous n\'avez pas créée.';

    // MESSAGES POUR LE TABLEAU
    const MESSAGE_DEJA_INSCRIT = 'Vous êtes déjà inscrit à cette sortie.';
    const MESSAGE_NON_OUVERTE = 'Vous ne pouvez pas vous inscrire à une sortie qui n\'est pas ouverte.';
    const MESSAGE_DATE_LIMITE_DEPASSEE = 'La date limite d\'inscription est dépassée.';
    const MESSAGE_SORTIE_COMPLETE = 'La sortie est complète.';
    const MESSAGE_INSCRIPTION_REUSSIE = 'Inscription à la sortie réussie !';

    private $em;
    private $sortieRep;

    public function __construct(
        EntityManagerInterface $em,
        SortieRepository $sortieRep,
        CampusRepository $campusRep,
        EtatRepository $etatRep,
        LieuRepository $lieuRep
    )
    {
        $this->em = $em;
        $this->sortieRep = $sortieRep;
        $this->campusRep = $campusRep;
        $this->etatRep = $etatRep;
        $this->lieuRep = $lieuRep;
    }

    #[Route('/lister', name: 'lister')]
    public function index(Request $request, HistoService $histoService): Response
    {
        $histoService->update();

        $etats = $this->etatRep->findAll();
        $campuses = $this->campusRep->findAll();

        $data = new SortieRecherche();
        $form = $this->createForm(SortieRechercheType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sorties = $this->sortieRep->rechercheParCritere($data, $this->getUser());
        }
        else {
            $sorties = $this->sortieRep->findAllWithRelations();
        }

        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
            'etats' => $etats,
            'campuses' => $campuses,
            'form' => $form
        ]);
    }

    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $sortie = $this->sortieRep->find($id);
        return $this->render('sortie/detailler.html.twig', ['sortie' => $sortie]);
    }

    #[Route('/creer', name: 'creer')]
    public function creer( Request $request ): Response
    {
        $sortie = new Sortie();
        if ($this->getUser() !== null) {$sortie->setCampus($this->getUser()->getCampus());}
        return $this->creerOuModifierForm($request, $sortie, self::MESSAGE_AJOUT_SUCCESS, true, );
    }

    #[Route('/modifier/{id}', name: 'modifier')]
    public function modifier(int $id, Request $request ): Response
    {
        $sortie = $this->sortieRep->find($id);
        return $this->creerOuModifierForm($request, $sortie, self::MESSAGE_MODIFICATION_SUCCESS, false, );
    }

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

        $sortie->setOrganisateur($this->getUser());
        $sortie->setEtat($this->etatRep->findOneBy(['libelle' => 'Creee']));
        if ($creation) { $sortie->addParticipant($this->getUser()); }
        $this->em->persist($sortie);
        $this->em->flush();

        return $this->redirectWithMessage('success', $message, $sortie->getId());
    }

    #[Route('/annuler/{id}', name: 'annuler', requirements: ['id' => '\d+'])]
    public function annuler(int $id): Response
    {
        $etatRep = $this->em->getRepository(Etat::class);
        $sortie = $this->sortieRep->find($id);

        if ($sortie->getOrganisateur() !== $this->getUser()) {
            return $this->redirectWithMessage('danger', self::MESSAGE_ANNULATION_IMPOSSIBLE, $id);
        }

        $sortie->setEtat($etatRep->findOneBy(['libelle' => 'Annulee']));
        $sortie->setInfosSortie("ANNULEE PAR L'ORGANISATEUR " . $sortie->getInfosSortie());
        $this->em->flush();
        return $this->redirectWithMessage('success', self::MESSAGE_ANNULATION_SUCCES, $id);
    }

    #[Route('/publier/{id}', name: 'publier', requirements: ['id' => '\d+'])]
    public function publier(int $id): Response
    {
        $etatRep = $this->em->getRepository(Etat::class);
        $sortie = $this->sortieRep->find($id);

        if ($sortie->getOrganisateur() !== $this->getUser()) {
            return $this->redirectWithMessage('danger',self::MESSAGE_PUBLICATION_IMPOSSIBLE, $id);
        }

        $sortie->setEtat($etatRep->findOneBy(['libelle' => 'Ouverte']));
        $this->em->flush();
        return $this->redirectWithMessage('success',self::MESSAGE_PUBLICATION_SUCCESS, $id);
    }

    #[Route('/inscrire/{id}', name: 'inscrire', requirements: ['id' => '\d+'])]
    public function inscrire(int $id): Response
    {
        $sortie = $this->sortieRep->find($id);
        $participant = $this->getUser();

        if ($sortie->getParticipants()->contains($participant)) {
            return $this->redirectWithMessage('warning', self::MESSAGE_DEJA_INSCRIT, $id);
        }

        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return $this->redirectWithMessage('danger', self::MESSAGE_NON_OUVERTE, $id);
        }

        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            return $this->redirectWithMessage('danger', self::MESSAGE_DATE_LIMITE_DEPASSEE, $id);
        }

        if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()) {
            return $this->redirectWithMessage('danger', self::MESSAGE_SORTIE_COMPLETE, $id);
        }

        $sortie->addParticipant($participant);
        $this->em->flush();
        return $this->redirectWithMessage('success', self::MESSAGE_INSCRIPTION_REUSSIE, $id);
    }

    #[Route('/desister/{id}', name: 'desister', requirements: ['id' => '\d+'])]
    public function desister(int $id): Response
    {
        $sortie = $this->sortieRep->find($id);
        $participant = $this->getUser();

        if (!$sortie->getParticipants()->contains($participant)) {
            return $this->redirectWithMessage('warning',self::MESSAGE_DESISTEMENT_NONINSCRIT, $id);
        }

        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return $this->redirectWithMessage('danger',self::MESSAGE_DESISTEMENT_IMPOSSIBLE, $id);
        }

        $sortie->removeParticipant($participant);
        $this->em->flush();
        return $this->redirectWithMessage('success',self::MESSAGE_DESISTEMENT_SUCCESS, $id);
    }

    #[Route('/lister/lieu/{idLieu}', name: 'lister_lieu')]
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
     * @param int $id L'identifiant de la sortie.
     * @return Response
     */
    private function redirectWithMessage($type, $message, $id): Response
    {
        $this->addFlash($type, $message);
        return $this->redirectToRoute('sortie_detailler', ['id' => $id]);
    }
}