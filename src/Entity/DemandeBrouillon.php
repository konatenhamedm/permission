<?php

namespace App\Entity;

use App\Repository\DemandeBrouillonRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeBrouillonRepository::class)]
class DemandeBrouillon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'demandeBrouillons')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motif = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::TEXT,nullable:true)]
    private ?string $motif_rejet_directeur = null;

    #[ORM\Column(type: Types::TEXT,nullable:true)]
    private ?string $motif_rejet_president = null;

    #[ORM\Column(type: Types::TEXT,nullable:true)]
    private ?string $motif_valider_president = null;

    #[ORM\ManyToOne(inversedBy: 'demandeBrouillons')]
    private ?Demande $demande = null;
    
        public function __construct()
        {
          $this->dateCreation = new DateTime();  
        }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getMotifRejetDirecteur(): ?string
    {
        return $this->motif_rejet_directeur;
    }

    public function setMotifRejetDirecteur(string $motif_rejet_directeur): static
    {
        $this->motif_rejet_directeur = $motif_rejet_directeur;

        return $this;
    }

    public function getMotifRejetPresident(): ?string
    {
        return $this->motif_rejet_president;
    }

    public function setMotifRejetPresident(string $motif_rejet_president): static
    {
        $this->motif_rejet_president = $motif_rejet_president;

        return $this;
    }

    public function getMotifValiderPresident(): ?string
    {
        return $this->motif_valider_president;
    }

    public function setMotifValiderPresident(string $motif_valider_president): static
    {
        $this->motif_valider_president = $motif_valider_president;

        return $this;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): static
    {
        $this->demande = $demande;

        return $this;
    }
}
