<?php

namespace App\Entity;

use App\Repository\LiquidacionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiquidacionRepository::class)]
class Liquidacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'liquidaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Propiedad $propiedad = null;

    #[ORM\Column(length: 7)]
    private string $periodo = '';

    #[ORM\Column(length: 20)]
    private string $estado = 'draft';

    #[ORM\Column]
    private int $total = 0;

    #[ORM\ManyToOne(inversedBy: 'liquidaciones')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Factura $factura = null;

    /** @var Collection<int, LiquidacionItem> */
    #[ORM\OneToMany(targetEntity: LiquidacionItem::class, mappedBy: 'liquidacion', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPropiedad(): ?Propiedad
    {
        return $this->propiedad;
    }

    public function setPropiedad(?Propiedad $propiedad): static
    {
        $this->propiedad = $propiedad;
        return $this;
    }

    public function getPeriodo(): string
    {
        return $this->periodo;
    }

    public function setPeriodo(string $periodo): static
    {
        $this->periodo = $periodo;
        return $this;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getFactura(): ?Factura
    {
        return $this->factura;
    }

    public function setFactura(?Factura $factura): static
    {
        $this->factura = $factura;
        return $this;
    }

    public function isFacturada(): bool
    {
        return $this->factura !== null;
    }

    /** @return Collection<int, LiquidacionItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(LiquidacionItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setLiquidacion($this);
        }
        return $this;
    }

    public function removeItem(LiquidacionItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getLiquidacion() === $this) {
                $item->setLiquidacion(null);
            }
        }
        return $this;
    }

    public function recalculate(): int
    {
        $cargos = 0;
        $descuentos = 0;

        foreach ($this->items as $item) {
            if ($item->getTipo() === 'cargo') {
                $cargos += $item->getMonto();
            } else {
                $descuentos += $item->getMonto();
            }
        }

        $this->total = $cargos - $descuentos;
        return $this->total;
    }

    public function isDraft(): bool
    {
        return $this->estado === 'draft';
    }

    public function isPaid(): bool
    {
        return $this->estado === 'paid';
    }
}
