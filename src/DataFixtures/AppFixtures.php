<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{

    private readonly Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $this->addLieu(10, $manager);
        $this->addEtat($manager);
        $this->addCampus($manager);
        $this->addParticipant(20, $manager);
        $this->addSortie(20, $manager);

        $manager->flush();
    }

    public function addLieu(int $number, ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($this->faker->country);
            $lieu->setRue($this->faker->streetAddress);
            $lieu->setLatitude($this->faker->latitude);
            $lieu->setLongitude($this->faker->longitude);
            $lieu->setNomDeVille($this->faker->city);
            $lieu->setCodePostal($this->faker->postcode);

            $manager->persist($lieu);
        }
        $manager->flush();
    }

    public function addEtat(ObjectManager $manager)
    {
        $etatNoms = ['Créee', 'Ouverte', 'Clôturée', 'Annulée', 'Historisée', 'Activité en cours', 'Passée'];

        foreach ($etatNoms as $nom) {
            $etat = new Etat();
            $etat->setLibelle($nom);
            $manager->persist($etat);
        }
        $manager->flush();
    }

    public function addCampus(ObjectManager $manager)
    {

        $campusNoms = ['Rennes', 'Niort', 'Nantes', 'Quimper', 'Campus en ligne'];

        foreach ($campusNoms as $nom) {
            $campusNoms = new Campus();
            $campusNoms->setNom($nom);
            $manager->persist($campusNoms);
        }
        $manager->flush();
    }

    public function addParticipant(int $number, ObjectManager $manager): void
    {
        // Récupérer tous les campus existants
        $campusRepository = $manager->getRepository(Campus::class);
        $campusList = $campusRepository->findAll();

        // Vérifier qu'il y a au moins un campus dans la base de données
        if (count($campusList) === 0) {
            throw new \Exception('Aucun campus trouvé dans la base de données.');
        }

        for ($i = 0; $i < 10; $i++) {
            $participant = new Participant();
            $participant->setEmail($this->faker->unique()->email);
            $participant->setRoles(['ROLE_USER']);
//            $password = $this->passwordHasher->hashPassword($participant, 'password');
            $participant->setPassword($this->faker->country);
            $participant->setNom($this->faker->lastName);
            $participant->setPrenom($this->faker->firstName);
            $participant->setTelephone($this->faker->optional()->phoneNumber);
            $participant->setPseudo($this->faker->userName);

            // Assigner un campus aléatoire parmi ceux récupérés
            $randomCampus = $this->faker->randomElement($campusList);
            $participant->setCampus($randomCampus);

            $manager->persist($participant);
        }

        $manager->flush();
    }

    public function addSortie(int $number, ObjectManager $manager): void
    {
        // Récupérer tous les lieux existants
        $lieuRepository = $manager->getRepository(Lieu::class);
        $lieuList = $lieuRepository->findAll();

        // Récupérer tous les états existants
        $etatRepository = $manager->getRepository(Etat::class);
        $etatList = $etatRepository->findAll();

        // Récupérer tous les participants existants
        $participantRepository = $manager->getRepository(Participant::class);
        $participantList = $participantRepository->findAll();

        // Récupérer tous les campus existants
        $campusRepository = $manager->getRepository(Campus::class);
        $campusList = $campusRepository->findAll();

        // Vérifier qu'il y a au moins un lieu, un état, un participant et un campus dans la base de données
        if (count($lieuList) === 0 || count($etatList) === 0 || count($participantList) === 0 || count($campusList) === 0) {
            throw new \Exception('Assurez-vous qu\'il y a au moins un lieu, un état, un participant et un campus dans la base de données.');
        }

        for ($i = 0; $i < 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($this->faker->sentence(3));
            $sortie->setDateHeureDebut($this->faker->dateTimeBetween("+5 day", "+10 day"));
            $sortie->setDuree($this->faker->numberBetween(60, 300));
            $sortie->setDateLimiteInscription($this->faker->dateTimeBetween("-15 day", "+2 day"));
            $sortie->setNbInscriptionsMax($this->faker->numberBetween(0, 5));
            $sortie->setInfosSortie($this->faker->paragraph);

            // Assigner un état, un lieu, un participant et un campus aléatoires
            $randomEtat = $this->faker->randomElement($etatList);
            $sortie->setEtat($randomEtat);

            $randomLieu = $this->faker->randomElement($lieuList);
            $sortie->setLieu($randomLieu);

            $randomParticipant = $this->faker->randomElement($participantList);
            $sortie->setOrganisateur($randomParticipant);

            $randomCampus = $this->faker->randomElement($campusList);
            $sortie->setCampus($randomCampus);

            // Ajouter des participants à la sortie
            for ($j = 0; $j < $this->faker->numberBetween(1, $sortie->getNbInscriptionsMax()); $j++) {
                $randomParticipant = $this->faker->randomElement($participantList);
                $sortie->addParticipant($randomParticipant);
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }
}
