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

    private $etatRep;

    public function __construct(ManagerRegistry $registry, EtatRepository $etatRep)
    {
        parent::__construct($registry, Sortie::class);
        $this->etatRep = $etatRep;
    }



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

    public function rechercheParCritere(SortieRecherche $sortieRecherche, Participant $user)
    {
        $sorties = $this->findAllWithRelations();
        return $this->filtrerSorties($sorties, $sortieRecherche, $user);
    }

    private function filtrerSorties(array $sorties, SortieRecherche $sortieRecherche, Participant $user): array
    {
        return array_filter($sorties, function(Sortie $sortie) use ($sortieRecherche, $user) {
            if (!empty($sortieRecherche->getNom()) && stripos($sortie->getNom(), $sortieRecherche->getNom()) === false) {
                return false;
            }

            if (!empty($sortieRecherche->getDateDebut()) && $sortie->getDateHeureDebut() < $sortieRecherche->getDateDebut()) {
                return false;
            }

            if (!empty($sortieRecherche->getDateFin())) {
                $dateFin = clone $sortieRecherche->getDateFin();
                $dateFin->setTime(23, 59, 59);
                if ($sortie->getDateHeureDebut() > $dateFin) {
                    return false;
                }
            }

            if (!empty($sortieRecherche->getEtat()) && $sortie->getEtat()->getId() !== $sortieRecherche->getEtat()->getId()) {
                return false;
            }

            if (!empty($sortieRecherche->getCampus()) && $sortie->getCampus()->getId() !== $sortieRecherche->getCampus()->getId()) {
                return false;
            }

            if (!empty($sortieRecherche->isOrganisateur()) && $sortie->getOrganisateur() !== $user) {
                return false;
            }

            if (!empty($sortieRecherche->isParticipant()) && !$sortie->getParticipants()->contains($user)) {
                return false;
            }

            if (!empty($sortieRecherche->isNotParticipant()) && $sortie->getParticipants()->contains($user)) {
                return false;
            }

            if (!empty($sortieRecherche->isPasse()) && $sortie->getEtat()->getLibelle() !== 'Passee') {
                return false;
            }

            return true;
        });
    }


    public function findSortiesForCloture(): array
    {
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
