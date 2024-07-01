<?php

namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClotureService
{
    private $sortieRepository;
    private $etatRepository;
    private $em;

    public function __construct(
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $em)
    {
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->em = $em;
    }

    public function update(): void
    {
        $sorties = $this->sortieRepository->findSortiesForCloture();
        $etats = $this->etatRepository->findAll();

        $dateDuJour = new \DateTime('now');
        $dateDuJour->format('Y-m-d H:i:s');

        $etatCloturee = null;
        $etatOuverte = null;
        $etatPassee = null;

        foreach ($etats as $etat) {
            if ($etat->getLibelle() == 'Cloturee') {
                $etatCloturee = $etat;
            }
            if ($etat->getLibelle() == 'Ouverte') {
                $etatOuverte = $etat;
            }
            if ($etat->getLibelle() == 'Passee') {
                $etatPassee = $etat;
            }
        }

        foreach ($sorties as $index => $sortie) {
            if ($index === 0) {
                dump($sortie);
            }
            $nombreInscriptionsMax = $sortie->getNbInscriptionsMax();
            $nombreInscriptions = count($sortie->getParticipants());
            $dateHeureDebut = $sortie->getDateHeureDebut();
            $dateLimiteInscription = $sortie->getDateLimiteInscription();
            $etat = $sortie->getEtat();


            if ($nombreInscriptionsMax === $nombreInscriptions && $etat->getLibelle() == 'Ouverte') {

                $sortie->setEtat($etatCloturee);
                $this->em->persist($sortie);

                $this->em->flush();
            }

            if ($dateDuJour > $dateLimiteInscription && $etat->getLibelle() == 'Ouverte') {
                $sortie->setEtat($etatCloturee);
                $this->em->persist($sortie);


            }
            if ($dateDuJour > $dateHeureDebut && $etat->getLibelle() == 'Ouverte') {
                $sortie->setEtat($etatPassee);
                $this->em->persist($sortie);
            }

        }

        $this->em->flush();


    }

}