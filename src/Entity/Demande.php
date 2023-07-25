<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotNull(message: "Le champs date debut est requis")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]

    private ?\DateTimeInterface $dateFin = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Blameable(on: 'create')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\OneToMany(mappedBy: 'demande', targetEntity: Motif::class, orphanRemoval: true, cascade: ['persist'])]
    #[Assert\NotBlank(message: 'Veuillez renseigner un pseudo')]
    private Collection $motifs;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Positive(message: 'Nous autorisons pas de valeur négative')]
    private ?string $nbreJour = null;

    #[ORM\ManyToOne(inversedBy: 'demandesAvis')]
    private ?Avis $avis = null;

    #[ORM\ManyToOne(inversedBy: 'demandesAvisPresident')]
    private ?AvisPresident $avisPresident = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificationDirecteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificationPresident = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Veillez selectionner un type")]
    private ?string $type = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alerte = null;

    #[ORM\OneToMany(mappedBy: 'demande', targetEntity: DemandeBrouillon::class)]
    private Collection $demandeBrouillons;

    public function __construct()
    {
        $this->motifs = new ArrayCollection();
        $this->demandeBrouillons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

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

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Motif>
     */
    public function getMotifs(): Collection
    {
        return $this->motifs;
    }

    public function addMotif(Motif $motif): self
    {
        if (!$this->motifs->contains($motif)) {
            $this->motifs->add($motif);
            $motif->setDemande($this);
        }

        return $this;
    }

    public function removeMotif(Motif $motif): self
    {
        if ($this->motifs->removeElement($motif)) {
            // set the owning side to null (unless already changed)
            if ($motif->getDemande() === $this) {
                $motif->setDemande(null);
            }
        }

        return $this;
    }

    public function getNbreJour(): ?string
    {
        return $this->nbreJour;
    }

    public function setNbreJour(string $nbreJour): self
    {
        $this->nbreJour = $nbreJour;

        return $this;
    }

    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): static
    {
        $this->avis = $avis;

        return $this;
    }

    public function getAvisPresident(): ?AvisPresident
    {
        return $this->avisPresident;
    }

    public function setAvisPresident(?AvisPresident $avisPresident): static
    {
        $this->avisPresident = $avisPresident;

        return $this;
    }

    public function getJustificationDirecteur(): ?string
    {
        return $this->justificationDirecteur;
    }

    public function setJustificationDirecteur(string $justificationDirecteur): static
    {
        $this->justificationDirecteur = $justificationDirecteur;

        return $this;
    }

    public function getJustificationPresident(): ?string
    {
        return $this->justificationPresident;
    }

    public function setJustificationPresident(string $justificationPresident): static
    {
        $this->justificationPresident = $justificationPresident;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut($heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin($heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }

    public function getAlerte(): ?string
    {
        return $this->alerte;
    }

    public function setAlerte(string $alerte): static
    {
        $this->alerte = $alerte;

        return $this;
    }

    /**
     * @return Collection<int, DemandeBrouillon>
     */
    public function getDemandeBrouillons(): Collection
    {
        return $this->demandeBrouillons;
    }

    public function addDemandeBrouillon(DemandeBrouillon $demandeBrouillon): static
    {
        if (!$this->demandeBrouillons->contains($demandeBrouillon)) {
            $this->demandeBrouillons->add($demandeBrouillon);
            $demandeBrouillon->setDemande($this);
        }

        return $this;
    }

    public function removeDemandeBrouillon(DemandeBrouillon $demandeBrouillon): static
    {
        if ($this->demandeBrouillons->removeElement($demandeBrouillon)) {
            // set the owning side to null (unless already changed)
            if ($demandeBrouillon->getDemande() === $this) {
                $demandeBrouillon->setDemande(null);
            }
        }

        return $this;
    }
}
