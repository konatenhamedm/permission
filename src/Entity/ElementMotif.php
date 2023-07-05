<?php

namespace App\Entity;

use App\Repository\ElementMotifRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElementMotifRepository::class)]
class ElementMotif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'element', targetEntity: Motif::class)]
    private Collection $motifs;


    public function __construct()
    {
        $this->motifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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
            $motif->setElement($this);
        }

        return $this;
    }

    public function removeMotif(Motif $motif): self
    {
        if ($this->motifs->removeElement($motif)) {
            // set the owning side to null (unless already changed)
            if ($motif->getElement() === $this) {
                $motif->setElement(null);
            }
        }

        return $this;
    }

}
