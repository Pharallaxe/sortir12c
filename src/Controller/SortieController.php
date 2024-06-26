<?php

namespace App\Controller;

use App\Entity\Campus;
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
    public function index(SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request): Response
    {
        $filterNom = $request->query->get('filter_nom');
        $filterDate = $request->query->get('filter_date');
        $filterEtat = $request->query->get('filter_etat');

        $queryBuilder = $sortieRepository->createQueryBuilder('s');

        if ($filterNom) {
            $queryBuilder->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$filterNom.'%');
        }

        if ($filterDate) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $filterDate)->setTime(0, 0, 0);
            $endDate = \DateTime::createFromFormat('Y-m-d', $filterDate)->setTime(23, 59, 59);
            if ($startDate && $endDate) {
                $queryBuilder->andWhere('s.dateHeureDebut BETWEEN :startDate AND :endDate')
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
            }
        }

        if ($filterEtat) {
            $queryBuilder->andWhere('s.etat = :etat')
                ->setParameter('etat', $filterEtat);
        }

        $sorties = $queryBuilder->getQuery()->getResult();
        $etats = $etatRepository->findAll();

        return $this->render('sortie/lister.html.twig', [
            'sorties' => $sorties,
            'etats' => $etats,
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
}
