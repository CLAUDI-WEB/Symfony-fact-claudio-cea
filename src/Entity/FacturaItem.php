<?php

namespace App\Entity;

use App\Repository\FacturaItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacturaItemRepository::class)]
class FacturaItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Factura $factura = null;

    #[ORM\Column(length: 180)]
    private string $descripcion = '';

    #[ORM\Column]
    private int $monto = 0;

    public function getId(): ?int { return $this->id; }

    public function getFactura(): ?Factura { return $this->factura; }
    public function setFactura(?Factura $factura): static { $this->factura = $factura; return $this; }

    public function getDescripcion(): string { return $this->descripcion; }
    public function setDescripcion(string $descripcion): static { $this->descripcion = $descripcion; return $this; }

    public function getMonto(): int { return $this->monto; }
    public function setMonto(int $monto): static { $this->monto = $monto; return $this; }
}
