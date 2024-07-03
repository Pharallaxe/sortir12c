<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\SortieRecherche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    private EtatRepository $etatRep;

    // Recherche du repository de l'entité Etat
    public function __construct(ManagerRegistry $registry, EtatRepository $etatRep)
    {
        parent::__construct($registry, Sortie::class);
        $this->etatRep = $etatRep;
    }

    // Récupérer toutes les sorties et leurs relations
    public function findAllWithRelations()
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.campus', 'c')
            ->addSelect('c')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();
    }

    // Récupérer les sorties en fonction des critères de recherche
    public function rechercheParCritere(
        SortieRecherche $sortieRecherche,
        Participant $user
    ): array
    {
        $sorties = $this->findAllWithRelations();
        return $this->filtrerSorties($sorties, $sortieRecherche, $user);
    }


    private function filtrerSorties(
        array $sorties,
        SortieRecherche $sortieRecherche,
        Participant $user
    ): array
    {
        return array_filter($sorties, function(Sortie $sortie) use ($sortieRecherche, $user) {
            // Vérifier si le nom de la sortie n'est pas vide et que le nom de
            // la sortie ne contient pas le nom de la sortie recherchée

            if (!empty($sortieRecherche->getNom()) &&
                stripos($sortie->getNom(), $sortieRecherche->getNom()) === false) {
                return false;
            }

            // Vérifier si la date de début de la sortie n'est pas vide et que
            // la date de début de la sortie est antérieure à la date de début de
            // la sortie
            if (!empty($sortieRecherche->getDateDebut()) &&
                $sortie->getDateHeureDebut() < $sortieRecherche->getDateDebut()) {
                return false;
            }

            // Vérifier si la date de fin de la sortie n'est pas vide et que la date
            // de fin de la sortie est postérieure à la date de fin de la recherche
            if (!empty($sortieRecherche->getDateFin())) {
                $dateFin = clone $sortieRecherche->getDateFin();
                $dateFin->setTime(23, 59, 59);
                if ($sortie->getDateHeureDebut() > $dateFin) {
                    return false;
                }
            }

            // Vérifier si l'état de la sortie n'est pas vide et que l'état de la sortie
            //est différent de l'état de la recherche
            if (!empty($sortieRecherche->getEtat()) &&
                $sortie->getEtat()->getId() !== $sortieRecherche->getEtat()->getId()) {
                return false;
            }
            // Vérifier si le campus de la sortie n'est pas vide et que le campus de la
            //sortie est différent du campus de la recherche
            if (!empty($sortieRecherche->getCampus()) &&
                $sortie->getCampus()->getId() !== $sortieRecherche->getCampus()->getId()) {
                return false;
            }

            // Vérifier si l'utilisateur est l'organisateur de la sortie
            if (!empty($sortieRecherche->isOrganisateur()) &&
                $sortie->getOrganisateur() !== $user) {
                return false;
            }

            // Vérifier si l'utilisateur est inscrit à la sortie
            if (!empty($sortieRecherche->isParticipant()) &&
                !$sortie->getParticipants()->contains($user)) {
                return false;
            }

            // Vérifier si l'utilisateur n'est pas inscrit à la sortie
            if (!empty($sortieRecherche->isNotParticipant())
                && $sortie->getParticipants()->contains($user)) {
                return false;
            }

            // Vérifier si la sortie est passée
            if (!empty($sortieRecherche->isPasse())
                && $sortie->getEtat()->getLibelle() !== 'Passee') {
                return false;
            }

            return true;
        });
    }


    //Récupérer les sorties qui doivent être cloturées
    public function findSortiesForCloture(): array
    {
        //Récupérer les sorties qui ont atteint le nombre maximum d'inscriptions, ou dont la date de début est passée, ou dont la date limite d'inscription est passée
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->groupBy('s.id')
            ->having('COUNT(p) = s.nbInscriptionsMax OR s.dateLimiteInscription < :now OR s.dateHeureDebut < :now')
            ->setParameter('now', new \DateTime('now'));

        return $qb->getQuery()->getResult();
    }
}