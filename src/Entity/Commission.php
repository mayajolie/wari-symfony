<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommissionRepository")
 */
class Commission
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $etat;

    /**
     * @ORM\Column(type="integer")
     */
    private $partenaire;

    /**
     * @ORM\Column(type="integer")
     */
    private $envoi;

    /**
     * @ORM\Column(type="integer")
     */
    private $retrait;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getPartenaire(): ?int
    {
        return $this->partenaire;
    }

    public function setPartenaire(int $partenaire): self
    {
        $this->partenaire = $partenaire;

        return $this;
    }

    public function getEnvoi(): ?int
    {
        return $this->envoi;
    }

    public function setEnvoi(int $envoi): self
    {
        $this->envoi = $envoi;

        return $this;
    }

    public function getRetrait(): ?int
    {
        return $this->retrait;
    }

    public function setRetrait(int $retrait): self
    {
        $this->retrait = $retrait;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
