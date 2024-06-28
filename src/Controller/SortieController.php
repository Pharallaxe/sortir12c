<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

    const MESSAGE_AJOUT_SUCCESS = 'Sortie ajoutée avec succès !' ;

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
    public function index(
        Request $request
    ): Response
    {
        $filterNom = $request->query->get('filter_nom');
        $filterDateFrom = $request->query->get('filter_date_from');
        $filterDateTo = $request->query->get('filter_date_to');
        $filterEtat = $request->query->get('filter_etat');
        $filterCampus = $request->query->get('filter_campus');
        $filterOrganisateur = $request->query->get('filter_organisateur');
        $filterParticipant = $request->query->get('filter_participant');
        $filterNotParticipant = $request->query->get('filter_not_participant');
        $filterPast = $request->query->get('filter_past');

        $queryBuilder = $this->sortieRep->createQueryBuilder('s');

        if ($filterNom) {
            $queryBuilder->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$filterNom.'%');
        }

        if ($filterDateFrom) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $filterDateFrom)->setTime(0, 0, 0);
            if ($startDate) {
                $queryBuilder->andWhere('s.dateHeureDebut >= :startDate')
                    ->setParameter('startDate', $startDate);
            }
        }

        if ($filterDateTo) {
            $endDate = \DateTime::createFromFormat('Y-m-d', $filterDateTo)->setTime(23, 59, 59);
            if ($endDate) {
                $queryBuilder->andWhere('s.dateHeureDebut <= :endDate')
                    ->setParameter('endDate', $endDate);
            }
        }

        if ($filterEtat) {
            $queryBuilder->andWhere('s.etat = :etat')
                ->setParameter('etat', $filterEtat);
        }

        if ($filterCampus) {
            $queryBuilder->andWhere('s.campus = :campus')
                ->setParameter('campus', $filterCampus);
        }

        if ($filterOrganisateur && $this->getUser()) {
            $queryBuilder->andWhere('s.organisateur = :user')
                ->setParameter('user', $this->getUser());
        }

        if ($filterParticipant && $this->getUser()) {
            $queryBuilder->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $this->getUser());
        }

        if ($filterNotParticipant && $this->getUser()) {
            $queryBuilder->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $this->getUser());
        }

        if ($filterPast) {
            $queryBuilder->andWhere('s.etat = :etatPast')
                ->setParameter('etatPast', $this->etatRep->findOneBy(['libelle' => 'passée']));
        }

        $sorties = $queryBuilder->getQuery()->getResult();
        $etats = $this->etatRep->findAll();
        $campuses = $this->campusRep->findAll();

        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
            'etats' => $etats,
            'campuses' => $campuses,
        ]);
    }

    #[Route('/detailler/{id}', name: 'detailler', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $sortie = $this->sortieRep->find($id);
        return $this->render('sortie/detailler.html.twig', ['sortie' => $sortie]);
    }

    #[Route('/creer', name: 'creer')]
    public function creer(
        Request $request,
    ): Response
    {
        $premierLieu = $this->lieuRep->trouverPremierLieuParOrdreAlphabetique();

        $sortie = new Sortie();
        if ($this->getUser() !== null) $sortie->setCampus($this->getUser()->getCampus());

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setOrganisateur($this->getUser());
            $sortie->setEtat($this->etatRep->findOneBy(['libelle' => 'Creee']));
            $sortie->addParticipant($this->getUser());
            $this->em->persist($sortie);
            $this->em->flush();

            $this->addFlash('success', self::MESSAGE_AJOUT_SUCCESS);
            return $this->redirectToRoute('sortie_detailler', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/creer.html.twig', [
            'sortieForm' => $sortieForm,
            'premierLieu' => $premierLieu
        ]);
    }

    #[Route('/modifier/{id}', name: 'modifier')]
    public function modifier(
        int $id,
        Request $request,
    ): Response
    {
        $sortie = $this->sortieRep->find($id);

        if (!$sortie) throw $this->createNotFoundException('Ooops ! sortie non trouvée !');

        $premierLieu = $this->lieuRep->trouverPremierLieuParOrdreAlphabetique();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setOrganisateur($this->getUser());
            $sortie->setEtat($this->etatRep->findOneBy(['libelle' => 'Creee']));
            $this->em->persist($sortie);
            $this->em->flush();

            $this->addFlash('success', self::MESSAGE_AJOUT_SUCCESS);
            return $this->redirectToRoute('sortie_detailler', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/modifier.html.twig', [
            'sortieModifierForm' => $sortieForm,
            'premierLieu' => $premierLieu
        ]);
    }

    #[Route('/annuler/{id}', name: 'annuler', requirements: ['id' => '\d+'])]
    public function annuler(int $id): Response
    {
        $etatRep = $this->em->getRepository(Etat::class);
        $sortie = $this->sortieRep->find($id);

        if ($sortie->getOrganisateur() === $this->getUser()) {
            $sortie->setEtat($etatRep->findOneBy(['libelle' => 'Annulee']));
            $sortie->setInfosSortie("ANNULEE PAR L'ORGANISATEUR " . $sortie->getInfosSortie());
            $this->em->flush();
            return $this->redirectWithMessage('success', self::MESSAGE_ANNULATION_SUCCES, $id);
        }

        return $this->redirectWithMessage('danger', self::MESSAGE_ANNULATION_IMPOSSIBLE, $id);
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

    #[Route('/publier/{id}', name: 'publier', requirements: ['id' => '\d+'])]
    public function publier(int $id): Response
    {
        $etatRep = $this->em->getRepository(Etat::class);
        $sortie = $this->sortieRep->find($id);

        if ($sortie->getOrganisateur() === $this->getUser()) {
            $sortie->setEtat($etatRep->findOneBy(['libelle' => 'Ouverte']));
            $this->em->flush();
            return $this->redirectWithMessage('success',self::MESSAGE_PUBLICATION_SUCCESS, $id);
        }

        return $this->redirectWithMessage('danger',self::MESSAGE_PUBLICATION_IMPOSSIBLE, $id);
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