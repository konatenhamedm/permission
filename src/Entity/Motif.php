<?php

namespace App\Entity;

use App\Repository\MotifRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MotifRepository::class)]
class Motif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomEnfant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $precisez = null;

    #[ORM\ManyToOne(inversedBy: 'motifs')]

    private ?ElementMotif $element = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'motifs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demande $demande = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Fichier $fichier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEnfant(): ?string
    {
        return $this->nomEnfant;
    }

    public function setNomEnfant(string $nomEnfant): self
    {
        $this->nomEnfant = $nomEnfant;

        return $this;
    }

    public function getPrecisez(): ?string
    {
        return $this->precisez;
    }

    public function setPrecisez($precisez): self
    {
        $this->precisez = $precisez;

        return $this;
    }

    public function getElement(): ?ElementMotif
    {
        return $this->element;
    }

    public function setElement(?ElementMotif $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): self
    {
        $this->demande = $demande;

        return $this;
    }

    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function setFichier(?Fichier $fichier): self
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(string $observation): self
    {
        $this->observation = $observation;

        return $this;
    }
}
