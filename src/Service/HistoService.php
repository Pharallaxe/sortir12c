<?php

namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class HistoService
{



    public function __construct(private SortieRepository $sortieRepository,
                                private EtatRepository $etatRepository,
                                private EntityManagerInterface $_em)
    {

    }

    // SortieRepository.php

    public function update(): void
    {
        $sorties = $this->sortieRepository->findAll();
        $etat = $this->etatRepository->find(5);

        foreach ($sorties as $sortie) {
            // Get the date one month ago from now
            $oneMonthAgo = new \DateTime('now');
            $oneMonthAgo->modify('-1 month');

            // Ensure $oneMonthAgo is set to midnight to accurately compare dates
            $oneMonthAgo->setTime(0, 0, 0);

            // Get the dateHeureDebut of Sortie and format it for comparison
            $dateHeureDebut = $sortie->getDateHeureDebut();
            $dateHeureDebutFormatted = $dateHeureDebut->format('Y-m-d H:i:s');

            // Compare dateHeureDebut with oneMonthAgo
            if ($dateHeureDebut < $oneMonthAgo) {
                // Update the etat of Sortie
                $sortie->setEtat($etat);

                // Persist changes
                $this->_em->persist($sortie);
            }
        }

        // Flush all changes
        $this->_em->flush();
    }


}