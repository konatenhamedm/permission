<?php

namespace App\Entity;

use App\Repository\PrestataireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestataireRepository::class)]
#[ORM\Table(name:'user_front_prestataire')]
class Prestataire extends UserFront
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $denominationSociale = null;

    #[ORM\Column(length: 255)]
    private ?string $logo = null;

    #[ORM\Column(length: 255)]
    private ?string $contactPrincipal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDenominationSociale(): ?string
    {
        return $this->denominationSociale;
    }

    public function setDenominationSociale(string $denominationSociale): static
    {
        $this->denominationSociale = $denominationSociale;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getContactPrincipal(): ?string
    {
        return $this->contactPrincipal;
    }

    public function setContactPrincipal(string $contactPrincipal): static
    {
        $this->contactPrincipal = $contactPrincipal;

        return $this;
    }
}
