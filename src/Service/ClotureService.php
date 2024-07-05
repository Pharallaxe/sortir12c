<?php

namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClotureService
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
        //Récupérer les sorties qui doivent être cloturées
//        $sorties = $this->sortieRepository->findSortiesForCloture();
        $sorties = $this->sortieRepository->findAllWithRelations();
        //Récupérer les états
        $etats = $this->etatRepository->findAll();

        //Récupérer la date du jour
        $dateDuJour = new \DateTime('now');
        $dateDuJour->format('Y-m-d H:i:s');

        $etatCloturee = null;
        $etatPassee = null;

        //Créer des variables pour pouvoir utiliser les états "Cloturee" et "Passee"
        foreach ($etats as $etat) {
            if ($etat->getLibelle() == 'Clôturée') {
                $etatCloturee = $etat;
            }

            if ($etat->getLibelle() == 'Passée') {
                $etatPassee = $etat;
            }
        }

        //Pour chaque sortie, si le nombre maximum d'inscriptions est atteint, ou si la date de début est passée, ou si la date limite d'inscription est passée, on change l'état de la sortie
        foreach ($sorties as $index => $sortie) {
            if ($index === 0) {
                dump($sortie);
            }
            $nombreInscriptionsMax = $sortie->getNbInscriptionsMax();
            $nombreInscriptions = count($sortie->getParticipants());
            $dateHeureDebut = $sortie->getDateHeureDebut();
            $dateLimiteInscription = $sortie->getDateLimiteInscription();
            $etat = $sortie->getEtat();

            //Si le nombre maximum d'inscriptions est atteint, on change l'état de la sortie à "Cloturee"
            if ($nombreInscriptionsMax === $nombreInscriptions && $etat->getLibelle() == 'Ouverte') {
                $sortie->setEtat($etatCloturee);
            }
            //Si la date de début est passée, on change l'état de la sortie à "Cloturee"
            if ($dateDuJour > $dateLimiteInscription && $etat->getLibelle() == 'Ouverte') {
                $sortie->setEtat($etatCloturee);
            }
            //Si la date limite d'inscription est passée, on change l'état de la sortie à "Passee"
            if ($dateDuJour > $dateHeureDebut && $etat->getLibelle() == 'Ouverte') {
                $sortie->setEtat($etatPassee);
            }
        }

        $this->em->flush();
    }
}