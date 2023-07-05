<?php

namespace App\Entity;

use App\Repository\AvisPresidentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisPresidentRepository::class)]
class AvisPresident
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'avisPresident', targetEntity: Demande::class)]
    private Collection $demandesAvisPresident;

    public function __construct()
    {
        $this->demandesAvisPresident = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        return $this->demandesAvisPresident;
    }

    public function addDemande(Demande $demande): static
    {
        if (!$this->demandesAvisPresident->contains($demande)) {
            $this->demandesAvisPresident->add($demande);
            $demande->setAvisPresident($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): static
    {
        if ($this->demandesAvisPresident->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getAvisPresident() === $this) {
                $demande->setAvisPresident(null);
            }
        }

        return $this;
    }
}
