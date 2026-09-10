<?php

namespace App\Entity;

use App\Repository\LiquidacionItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiquidacionItemRepository::class)]
class LiquidacionItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Liquidacion $liquidacion = null;

    /** cargo | descuento */
    #[ORM\Column(length: 20)]
    private string $tipo = 'cargo';

    #[ORM\Column(length: 180)]
    private string $descripcion = '';

    /** siempre positivo; el signo lo da el tipo */
    #[ORM\Column]
    private int $monto = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLiquidacion(): ?Liquidacion
    {
        return $this->liquidacion;
    }

    public function setLiquidacion(?Liquidacion $liquidacion): static
    {
        $this->liquidacion = $liquidacion;
        return $this;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getMonto(): int
    {
        return $this->monto;
    }

    public function setMonto(int $monto): static
    {
        $this->monto = $monto;
        return $this;
    }

    public function isCargo(): bool
    {
        return $this->tipo === 'cargo';
    }

    public function isDescuento(): bool
    {
        return $this->tipo === 'descuento';
    }
}
