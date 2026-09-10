<?php

namespace App\Entity;

use App\Repository\EmpresaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpresaRepository::class)]
class Empresa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 12)]
    private ?string $rut = null;

    #[ORM\Column(length: 150)]
    private ?string $razonSocial = null;

    #[ORM\Column(length: 80)]
    private ?string $giro = null;

    #[ORM\Column(length: 120)]
    private ?string $direccion = null;

    #[ORM\Column(length: 50)]
    private ?string $comuna = null;

    #[ORM\Column(length: 50)]
    private ?string $ciudad = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /** @var Collection<int, Propiedad> */
    #[ORM\OneToMany(targetEntity: Propiedad::class, mappedBy: 'empresa')]
    private Collection $propiedades;

    public function __construct()
    {
        $this->propiedades = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(string $rut): static
    {
        $this->rut = $rut;
        return $this;
    }

    public function getRazonSocial(): ?string
    {
        return $this->razonSocial;
    }

    public function setRazonSocial(string $razonSocial): static
    {
        $this->razonSocial = $razonSocial;
        return $this;
    }

    public function getGiro(): ?string
    {
        return $this->giro;
    }

    public function setGiro(string $giro): static
    {
        $this->giro = $giro;
        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): static
    {
        $this->direccion = $direccion;
        return $this;
    }

    public function getComuna(): ?string
    {
        return $this->comuna;
    }

    public function setComuna(string $comuna): static
    {
        $this->comuna = $comuna;
        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): static
    {
        $this->ciudad = $ciudad;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /** @return Collection<int, Propiedad> */
    public function getPropiedades(): Collection
    {
        return $this->propiedades;
    }

    public function addPropiedad(Propiedad $propiedad): static
    {
        if (!$this->propiedades->contains($propiedad)) {
            $this->propiedades->add($propiedad);
            $propiedad->setEmpresa($this);
        }
        return $this;
    }

    public function removePropiedad(Propiedad $propiedad): static
    {
        if ($this->propiedades->removeElement($propiedad)) {
            if ($propiedad->getEmpresa() === $this) {
                $propiedad->setEmpresa(null);
            }
        }
        return $this;
    }
}
