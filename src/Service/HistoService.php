<?php

namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class HistoService
{
    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;
    private EntityManagerInterface $em;

    //Mettre en place des outils nécessaires pour le service
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
        //Récupérer toutes les sorties
        $sorties = $this->sortieRepository->findAll();
        //Récupérer l'état "Historis"
        $etat = $this->etatRepository->find(5);

        //Pour chaque sortie, si la date de début est plus vielle q'un mois, on change l'état de la sortie à "Historisee"
        foreach ($sorties as $sortie) {
            $oneMonthAgo = new \DateTime('now');
            $oneMonthAgo->modify('-1 month');
            $oneMonthAgo->setTime(0, 0, 0);
            $dateHeureDebut = $sortie->getDateHeureDebut();


            if ($dateHeureDebut < $oneMonthAgo) {
                $sortie->setEtat($etat);
            }
        }

        $this->em->flush();
    }
}