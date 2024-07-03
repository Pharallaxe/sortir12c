<?php

namespace App\Tests;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FirstTest extends WebTestCase
{
    private $entityManager;


    // Récupération de l'entity manager
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

    }

    // Test de création d'une sortie
    public function testSomething(): void
    {
        // Création d'une sortie
        $sortie = new Sortie();
        $sortie->setNom('Sortie test');
        $sortie->setDateHeureDebut(new \DateTime('2021-06-01 12:00:00'));
        $sortie->setDateLimiteInscription(new \DateTime('2021-05-31 12:00:00'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setDuree(120);
        $sortie->setInfosSortie('Infos test');
        $sortie->setEtat($this->entityManager->getRepository(Etat::class)->find(1));
        $sortie->setCampus($this->entityManager->getRepository(Campus::class)->find(1));
        $sortie->setLieu($this->entityManager->getRepository(Lieu::class)->find(1));

        $organisateur = $this->entityManager->getRepository(Participant::class)->find(1);
        $sortie->setOrganisateur($organisateur);

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        // Récupération de toutes les sorties
        $allSorties = $this->entityManager->getRepository(Sortie::class)->findAll();
        $sortieFound = false;

        // Vérification que la sortie a bien été créée
        foreach ($allSorties as $sortie) {
            if ($sortie->getNom() == 'Sortie test') {
                $sortieFound = true;
                break;
            }
        }

        $this->assertTrue($sortieFound, 'The new Sortie was not found in the database.');
    }


}

