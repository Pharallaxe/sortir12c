<?php

// src/Entity/SearchCriteria.php
namespace App\Entity;

use DateTime;

class SortieRecherche
{
    private ?string $nom = null;
    private ?DateTime $dateDebut = null;
    private ?DateTime $dateFin = null;
    private ?Etat $etat = null;
    private ?Campus $campus = null;
    private bool $organisateur = false;
    private bool $participant = false;
    private bool $notParticipant = false;
    private bool $passe = false;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDateDebut(): ?DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?DateTime $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?DateTime $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function isOrganisateur(): bool
    {
        return $this->organisateur;
    }

    public function isParticipant(): bool
    {
        return $this->participant;
    }

    public function setParticipant(bool $participant): self
    {
        $this->participant = $participant;
        return $this;
    }

    public function isNotParticipant(): bool
    {
        return $this->notParticipant;
    }

    public function setNotParticipant(bool $notParticipant): self
    {
        $this->notParticipant = $notParticipant;
        return $this;
    }

    public function isPasse(): bool
    {
        return $this->passe;
    }

    public function setPasse(bool $passe): self
    {
        $this->passe = $passe;
        return $this;
    }
}
