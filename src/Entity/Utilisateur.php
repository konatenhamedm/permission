<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(['username'], message: 'Ce pseudo est déjà utilisé')]
#[ORM\Table(name: 'user_utilisateur')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un pseudo')]
    private ?string $username = null;



    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez renseigner le mot de passe', groups: ['Registration'])]
    private ?string $password = null;

    #[ORM\OneToOne(inversedBy: "utilisateur", cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner un employé', groups: ['Registration'])]
    private ?Employe $employe = null;

    /* #[ORM\ManyToMany(targetEntity: Groupe::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(name: 'user_utilisateur_groupe')]
    private Collection $groupes;*/

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $resetToken;


    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Groupe $groupe = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Demande::class)]
    private Collection $demandes;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: DemandeBrouillon::class)]
    private Collection $demandeBrouillons;

    public function __construct()
    {
        // $this->groupes = new ArrayCollection();
        $this->demandes = new ArrayCollection();
        $this->demandeBrouillons = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of resetToken
     */
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    /**
     * Set the value of resetToken
     *
     * @return  self
     */
    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = (array)$this->roles;
        $roles[] = 'ROLE_USER';
        /*foreach ($this->getGroupes() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        } */

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * {@inheritDoc}
     */
    public function isEqualTo(UserInterface $user): bool
    {
        return $this->getUsername() == $user->getUserIdentifier();
    }

    public function getEmploye(): ?Employe
    {
        return $this->employe;
    }

    public function setEmploye(Employe $employe): self
    {
        $this->employe = $employe;

        return $this;
    }



    public function hasRoleOnModuleChild($module, $child)
    {
        $module = strtoupper($module);
        $child = strtoupper($child);
        $result = false;
        foreach ($this->getRoles() as $role) {
            if (preg_match("/^ROLE_([A-Z_]+)_{$module}_([A-Z_]+)_{$child}/", $role)) {
                $result = true;
                break;
            }
        }
        return $this->hasRole('ROLE_ADMIN') || $result;
    }


    public function hasRoleOnAlias($module, $alias, $roleName)
    {
        $roleAlias = strtoupper(strtr($alias, '.', '_'));
        $role = "{$roleName}_{$module}_{$roleAlias}";

        return $this->hasRole('ROLE_ADMIN') || $this->hasRole($role);
    }


    public function hasRoleNameOnModuleChild($roleName, $module, $child)
    {
        $module = strtoupper($module);
        $child = strtoupper($child);
        $roleName = strtoupper($roleName);
        $result = false;

        foreach ($this->getRoles() as $role) {
            $regex = "^ROLE_{$roleName}_{$module}_([A-Z_]+)";
            if ($child) {
                $regex .= "_{$child}";
            }

            if (preg_match("/{$regex}/", $role)) {
                $result = true;
                break;
            }
        }
        return $this->hasRole('ROLE_ADMIN') || $result;
    }


    public function hasRoleOnModuleController($module,  $controller)
    {
        $module = strtoupper($module);
        $controller = strtoupper($controller);
        $result = false;
        foreach ($this->getRoles() as $role) {
            if (preg_match("/^ROLE_([A-Z_]+)_{$module}_{$controller}/", $role)) {
                $result = true;
                break;
            }
        }
        return $this->hasRole('ROLE_ADMIN') || $result;
    }


    public function hasRoleOnModuleControllers($module,  array $controllers)
    {
        $module = strtoupper($module);
        $controllers = array_map(function ($controller) {
            return strtoupper($controller);
        }, $controllers);
        $lsControllers = implode('|', $controllers);
        $result = false;
        foreach ($this->getRoles() as $role) {
            if (preg_match("/^ROLE_([A-Z_]+)_{$module}_({$lsControllers})/", $role)) {
                $result = true;
                break;
            }
        }
        return $this->hasRole('ROLE_ADMIN') || $result;
    }



    public function hasAllRoleOnModule($roleName, $module, $controller, $child = null, $as = null)
    {
        $module = strtoupper($module);

        $roleName = strtoupper($roleName);
        $controller = $as ? strtoupper($as) : strtoupper($controller);
        $result = false;




        foreach ($this->getRoles() as $role) {
            $regex = "^ROLE_{$roleName}_{$module}_{$controller}";

            if ($child) {
                $regex .= strtoupper("_{$child}");
            }

            if (preg_match("/{$regex}$/", $role)) {
                $result = true;
                break;
            }
        }
        return $this->hasRole('ROLE_ADMIN') || $result;
    }



    public function hasRoleStartsWith($roleName)
    {
        $result = false;

        foreach ($this->getRoles() as $role) {
            if (preg_match("/^{$roleName}/", $role, $matches)) {
                $result = true;
                break;
            }
        }
        return $this->hasRole('ROLE_ADMIN') || $result;
    }

    public function hasRoleOnModule(string $module, $exclude = null, $append = null)
    {
        $module = strtoupper($module);
        $result = false;

        $exclude = (array)$exclude;


        foreach ($this->getRoles() as $role) {
            $regex = "/^ROLE_([A-Z_]+)_{$module}_";

            // dd($regex);
            if ($append) {
                $regex .= strtoupper($append);
            }
            $regex .= "/";

            if (preg_match($regex, $role, $matches)) {
                $lowerMatch = strtolower($matches[1]);
                if (!$exclude || ($exclude &&  !in_array($lowerMatch, $exclude))) {
                    $result = true;
                    break;
                }
            }
        }
        return $this->hasRole('ROLE_ADMIN') || $result;
    }


    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === 'ROLE_USER') {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }



    /**
     * @param $roles
     */
    public function hasRoles($roles)
    {
        return array_intersect($this->getRoles(), $roles);
    }


    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function getNomComplet()
    {
        return $this->getEmploye() ? $this->getEmploye()->getNomComplet() : '';
    }



    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): self
    {
        $this->groupe = $groupe;

        return $this;
    }


    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getUtilisateur() === $this) {
                $demande->setUtilisateur(null);
            }
        }

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
            $demandeBrouillon->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDemandeBrouillon(DemandeBrouillon $demandeBrouillon): static
    {
        if ($this->demandeBrouillons->removeElement($demandeBrouillon)) {
            // set the owning side to null (unless already changed)
            if ($demandeBrouillon->getUtilisateur() === $this) {
                $demandeBrouillon->setUtilisateur(null);
            }
        }

        return $this;
    }
}
