<?php

namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class HistoService
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
        $sorties = $this->sortieRepository->findAll();
        $etat = $this->etatRepository->find(5);

        foreach ($sorties as $sortie) {
            $oneMonthAgo = new \DateTime('now');
            $oneMonthAgo->modify('-1 month');
            $oneMonthAgo->setTime(0, 0, 0);
            $dateHeureDebut = $sortie->getDateHeureDebut();
            $dateHeureDebutFormatted = $dateHeureDebut->format('Y-m-d H:i:s');

            if ($dateHeureDebut < $oneMonthAgo) {
                $sortie->setEtat($etat);
                $this->em->persist($sortie);
            }
        }

        $this->em->flush();
    }
}