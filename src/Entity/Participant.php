<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['pseudo'], message: 'Il existe déjà un compte avec ce pseudo')]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cette adresse email')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre adresse mail')]
    #[Assert\Email(message: "L'adresse email {{ value }} n'est pas une adresse mail valide")]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom')]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/u',
        message: "Le nom ne peut contenir que des lettres, des espaces et des tirets."
    )]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre prénom')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s-]+$/u',
        message: "Le prénom ne peut contenir que des lettres, des espaces et des tirets."
    )]
    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[Assert\Regex(pattern: '/^\+?[0-9\s\-\(\)]+$/', message: "Le numéro de téléphone n'est pas valide.")]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre pseudo')]
    #[ORM\Column(length: 20, unique: true)]
    private ?string $pseudo = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre campus')]
    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur')]
    private Collection $estOrganisateur;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $estInscrit;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageProfile = null;

    public function __construct()
    {
        $this->estOrganisateur = new ArrayCollection();
        $this->estInscrit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getEstOrganisateur(): Collection
    {
        return $this->estOrganisateur;
    }

    public function addEstOrganisateur(Sortie $estOrganisateur): static
    {
        if (!$this->estOrganisateur->contains($estOrganisateur)) {
            $this->estOrganisateur->add($estOrganisateur);
            $estOrganisateur->setOrganisateur($this);
        }

        return $this;
    }

    public function removeEstOrganisateur(Sortie $estOrganisateur): static
    {
        if ($this->estOrganisateur->removeElement($estOrganisateur)) {
            // set the owning side to null (unless already changed)
            if ($estOrganisateur->getOrganisateur() === $this) {
                $estOrganisateur->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getEstInscrit(): Collection
    {
        return $this->estInscrit;
    }

    public function addEstInscrit(Sortie $estInscrit): static
    {
        if (!$this->estInscrit->contains($estInscrit)) {
            $this->estInscrit->add($estInscrit);
        }

        return $this;
    }

    public function removeEstInscrit(Sortie $estInscrit): static
    {
        $this->estInscrit->removeElement($estInscrit);

        return $this;
    }

    public function getImageProfile(): ?string
    {
        return $this->imageProfile;
    }

    public function setImageProfile(?string $imageProfile): static
    {
        $this->imageProfile = $imageProfile;

        return $this;
    }
}
