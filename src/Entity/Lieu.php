<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner un nom de lieu')]
    #[Assert\Length(max: 30, maxMessage: 'Maximum {{ limit }} caractères')]
    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner une rue')]
    #[Assert\Length(max: 255, maxMessage: 'Maximum {{ limit }} caractères')]
    #[ORM\Column(length: 255)]
    private ?string $rue = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner une nom de ville')]
    #[Assert\Length(max: 255, maxMessage: 'Maximum {{ limit }} caractères')]
    #[ORM\Column(length: 255)]
    private ?string $nomDeVille = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner un code postal')]
    #[Assert\Length(max: 5, maxMessage: 'Maximum {{ limit }} caractères')]
    #[ORM\Column(length: 5)]
    private ?string $codePostal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getNomDeVille(): ?string
    {
        return $this->nomDeVille;
    }

    public function setNomDeVille(string $nomDeVille): static
    {
        $this->nomDeVille = $nomDeVille;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }
}
