<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use FOS\RestBundle\Validator\Constraints\Regex;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PartenairesRepository")
 */
class Partenaires
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $raisonSocial;
    /**
     * @ORM\Column(name="ninea",type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Vous devez insérer un ninea")
     */
    private $ninea;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adresse;

    /**
     * @ORM\Column(name="telephone",type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Vous devez insérer un téléphone")
     * @Assert\Regex(
     *     pattern="/^(\+[1-9][0-9]*(\([0-9]*\)|-[0-9]*-))?[0]?[1-9][0-9\-]*$/",
     *     match=true,
     *     message="Votre numero ne doit pas contenir de lettre"
     * )
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etat;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="partenaire")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ComptBancaire", mappedBy="partenaire")
     */
    private $comptBancaires;

   

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->comptBancaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSocial(): ?string
    {
        return $this->raisonSocial;
    }

    public function setRaisonSocial(string $raisonSocial): self
    {
        $this->raisonSocial = $raisonSocial;

        return $this;
    }

    public function getNinea(): ?string
    {
        return $this->ninea;
    }

    public function setNinea(string $ninea): self
    {
        $this->ninea = $ninea;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

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
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setPartenaire($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getPartenaire() === $this) {
                $user->setPartenaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ComptBancaire[]
     */
    public function getComptBancaires(): Collection
    {
        return $this->comptBancaires;
    }

    public function addComptBancaire(ComptBancaire $comptBancaire): self
    {
        if (!$this->comptBancaires->contains($comptBancaire)) {
            $this->comptBancaires[] = $comptBancaire;
            $comptBancaire->setPartenaire($this);
        }

        return $this;
    }

    public function removeComptBancaire(ComptBancaire $comptBancaire): self
    {
        if ($this->comptBancaires->contains($comptBancaire)) {
            $this->comptBancaires->removeElement($comptBancaire);
            // set the owning side to null (unless already changed)
            if ($comptBancaire->getPartenaire() === $this) {
                $comptBancaire->setPartenaire(null);
            }
        }

        return $this;
    }

    
}
