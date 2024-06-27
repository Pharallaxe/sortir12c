<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties/lister', name: 'sortie_lister')]
    public function index(SortieRepository $sortieRepository, EtatRepository $etatRepository, CampusRepository $campusRepository, Request $request): Response
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

        $queryBuilder = $sortieRepository->createQueryBuilder('s');

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
                ->setParameter('etatPast', $etatRepository->findOneBy(['libelle' => 'passée']));
        }

        $sorties = $queryBuilder->getQuery()->getResult();
        $etats = $etatRepository->findAll();
        $campuses = $campusRepository->findAll();

        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
            'etats' => $etats,
            'campuses' => $campuses,
        ]);
    }

    #[Route('/sorties/detailler/{id}', name: 'sortie_detailler', requirements: ['id' => '\d+'])]
    public function detail(
        int                    $id,
        SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('sortie/detailler.html.twig', [
            'sortie' => $sortie,
        ]);
    }


    #[Route('/sorties/creer', name: 'sortie_creer')]
    public function creer(
        EntityManagerInterface $entityManager,
        Request $request,
        EtatRepository $etatRepository
    ): Response
    {

        $sortie = new Sortie();
        if ($this->getUser() !== null) {
            $sortie->setCampus($this->getUser()->getCampus());
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setOrganisateur($this->getUser());
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Creee']));
            $sortie->addParticipant($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée avec succès !');
            return $this->redirectToRoute('sortie_detailler', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/creer.html.twig', [
            'sortieForm' => $sortieForm,
        ]);
    }

    #[Route('/sorties/inscrire/{id}', name: 'sortie_inscrire', requirements: ['id' => '\d+'])]
    public function inscrire(
        int $id,
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository
    ): Response
    {
        $sortie = $sortieRepository->find($id);
        $participant = $this->getUser();

        if ($sortie->getParticipants()->contains($participant)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
        }
        elseif ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            $this->addFlash('danger', 'Vous ne pouvez pas vous inscrire à une sortie qui n\'est pas ouverte.');
        }
        elseif ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('danger', 'La date limite d\'inscription est dépassée.');
        }
        elseif ($sortie->getParticipants().count() >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('danger', 'La sortie est complète.');
        }

        else {
            $sortie->addParticipant($participant);
            $entityManager->flush();
            $this->addFlash('success', 'Inscription à la sortie réussie !');
        }

        return $this->redirectToRoute('sortie_detailler', ['id' => $id]);
    }

    #[Route('/sorties/publier/{id}', name: 'sortie_publier', requirements: ['id' => '\d+'])]
    public function publier(
        int $id,
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository
    ): Response
    {
        $etatRepository = $entityManager->getRepository(Etat::class);

        $sortie = $sortieRepository->find($id);


        if ($sortie->getOrganisateur() === $this->getUser()) {

            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            $entityManager->flush();
            $this->addFlash('success', 'Sortie publiée avec succès !');
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas publier une sortie que vous n\'avez pas créée.');
        }

        return $this->redirectToRoute('sortie_detailler', ['id' => $id]);
    }

}
