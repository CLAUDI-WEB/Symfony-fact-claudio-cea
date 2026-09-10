<?php

namespace App\Entity;

use App\Repository\FacturaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacturaRepository::class)]
class Factura
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empresa $empresa = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Arrendatario $arrendatario = null;

    #[ORM\Column(length: 7)]
    private string $periodo = '';

    #[ORM\Column]
    private int $folio = 1;

    #[ORM\Column]
    private int $tipoDte = 33;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $fechaEmision = null;

    #[ORM\Column]
    private int $neto = 0;

    #[ORM\Column]
    private int $iva = 0;

    #[ORM\Column]
    private int $exento = 0;

    #[ORM\Column]
    private int $total = 0;

    #[ORM\Column(type: Types::TEXT)]
    private string $archivoPlano = '';

    /** @var Collection<int, FacturaItem> */
    #[ORM\OneToMany(targetEntity: FacturaItem::class, mappedBy: 'factura', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    /** @var Collection<int, Liquidacion> */
    #[ORM\OneToMany(targetEntity: Liquidacion::class, mappedBy: 'factura')]
    private Collection $liquidaciones;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->liquidaciones = new ArrayCollection();
        $this->fechaEmision = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): static { $this->empresa = $empresa; return $this; }

    public function getArrendatario(): ?Arrendatario { return $this->arrendatario; }
    public function setArrendatario(?Arrendatario $arrendatario): static { $this->arrendatario = $arrendatario; return $this; }

    public function getPeriodo(): string { return $this->periodo; }
    public function setPeriodo(string $periodo): static { $this->periodo = $periodo; return $this; }

    public function getFolio(): int { return $this->folio; }
    public function setFolio(int $folio): static { $this->folio = $folio; return $this; }

    public function getTipoDte(): int { return $this->tipoDte; }
    public function setTipoDte(int $tipoDte): static { $this->tipoDte = $tipoDte; return $this; }

    public function getFechaEmision(): ?\DateTimeImmutable { return $this->fechaEmision; }
    public function setFechaEmision(\DateTimeImmutable $fechaEmision): static { $this->fechaEmision = $fechaEmision; return $this; }

    public function getNeto(): int { return $this->neto; }
    public function setNeto(int $neto): static { $this->neto = $neto; return $this; }

    public function getIva(): int { return $this->iva; }
    public function setIva(int $iva): static { $this->iva = $iva; return $this; }

    public function getExento(): int { return $this->exento; }
    public function setExento(int $exento): static { $this->exento = $exento; return $this; }

    public function getTotal(): int { return $this->total; }
    public function setTotal(int $total): static { $this->total = $total; return $this; }

    public function getArchivoPlano(): string { return $this->archivoPlano; }
    public function setArchivoPlano(string $archivoPlano): static { $this->archivoPlano = $archivoPlano; return $this; }

    /** @return Collection<int, FacturaItem> */
    public function getItems(): Collection { return $this->items; }

    public function addItem(FacturaItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setFactura($this);
        }
        return $this;
    }

    public function removeItem(FacturaItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getFactura() === $this) {
                $item->setFactura(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Liquidacion> */
    public function getLiquidaciones(): Collection { return $this->liquidaciones; }
}
