<?php
namespace App\Entity;
use App\Repository\PropiedadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: PropiedadRepository::class)]
class Propiedad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 150)]
    private ?string $nombre = null;
    #[ORM\Column(length: 180)]
    private ?string $direccion = null;
    #[ORM\ManyToOne(inversedBy: 'propiedades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empresa $empresa = null;
    #[ORM\ManyToOne(inversedBy: 'propiedades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Arrendatario $arrendatario = null;
    /** @var Collection<int, Liquidacion> */
    #[ORM\OneToMany(targetEntity: Liquidacion::class, mappedBy: 'propiedad')]
    private Collection $liquidaciones;
    public function __construct()
    {
        $this->liquidaciones = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNombre(): ?string
    {
        return $this->nombre;
    }
    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
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
    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }
    public function setEmpresa(?Empresa $empresa): static
    {
        $this->empresa = $empresa;
        return $this;
    }
    public function getArrendatario(): ?Arrendatario
    {
        return $this->arrendatario;
    }
    public function setArrendatario(?Arrendatario $arrendatario): static
    {
        $this->arrendatario = $arrendatario;
        return $this;
    }
    /** @return Collection<int, Liquidacion> */
    public function getLiquidaciones(): Collection
    {
        return $this->liquidaciones;
    }
    public function addLiquidacion(Liquidacion $liquidacion): static
    {
        if (!$this->liquidaciones->contains($liquidacion)) {
            $this->liquidaciones->add($liquidacion);
            $liquidacion->setPropiedad($this);
        }
        return $this;
    }
    public function removeLiquidacion(Liquidacion $liquidacion): static
    {
        if ($this->liquidaciones->removeElement($liquidacion)) {
            if ($liquidacion->getPropiedad() === $this) {
                $liquidacion->setPropiedad(null);
            }
        }
        return $this;
    }
}
