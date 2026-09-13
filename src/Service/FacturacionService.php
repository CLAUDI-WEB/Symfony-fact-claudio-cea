<?php

namespace App\Service;

use App\Entity\Factura;
use App\Entity\FacturaItem;
use App\Entity\Liquidacion;
use App\Repository\FacturaRepository;
use App\Repository\LiquidacionRepository;
use Doctrine\ORM\EntityManagerInterface;

class FacturacionService
{
    public function __construct(
        private readonly LiquidacionRepository $liquidacionRepository,
        private readonly FacturaRepository $facturaRepository,
        private readonly ArchivoPlanoGenerator $archivoPlanoGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function preview(string $periodo): array
    {
        $this->validatePeriodo($periodo);

        $liquidaciones = $this->liquidacionRepository
            ->findPendientesFacturacion($periodo);

        if ($liquidaciones === []) {
            throw new \DomainException(
                sprintf(
                    'No hay liquidaciones pendientes para el período %s.',
                    $periodo,
                )
            );
        }

        $neto = array_sum(
            array_map(
                static fn (Liquidacion $liquidacion): int =>
                    $liquidacion->getTotal(),
                $liquidaciones,
            )
        );

        $iva = (int) round($neto * 0.19);

        return [
            'periodo' => $periodo,
            'liquidaciones' => $liquidaciones,
            'cantidad' => count($liquidaciones),
            'neto' => $neto,
            'iva' => $iva,
            'exento' => 0,
            'total' => $neto + $iva,
        ];
    }

    public function crear(string $periodo): Factura
    {
        return $this->entityManager->wrapInTransaction(
            function () use ($periodo): Factura {
                // Se vuelve a consultar para evitar usar un preview antiguo.
                $preview = $this->preview($periodo);

                /** @var Liquidacion[] $liquidaciones */
                $liquidaciones = $preview['liquidaciones'];

                $primera = $liquidaciones[0];
                $propiedad = $primera->getPropiedad();

                if ($propiedad === null) {
                    throw new \DomainException(
                        'La liquidación no tiene propiedad.'
                    );
                }

                $empresa = $propiedad->getEmpresa();
                $arrendatario = $propiedad->getArrendatario();

                if ($empresa === null || $arrendatario === null) {
                    throw new \DomainException(
                        'Faltan datos del emisor o del receptor.'
                    );
                }

                $factura = (new Factura())
                    ->setEmpresa($empresa)
                    ->setArrendatario($arrendatario)
                    ->setPeriodo($periodo)
                    ->setFolio($this->facturaRepository->getNextFolio())
                    ->setTipoDte(33)
                    ->setFechaEmision(new \DateTimeImmutable())
                    ->setNeto($preview['neto'])
                    ->setIva($preview['iva'])
                    ->setExento($preview['exento'])
                    ->setTotal($preview['total']);

                foreach ($liquidaciones as $liquidacion) {
                    if (
                        $liquidacion->getPropiedad()?->getEmpresa() !== $empresa
                        || $liquidacion->getPropiedad()?->getArrendatario()
                            !== $arrendatario
                    ) {
                        throw new \DomainException(
                            'Las liquidaciones no corresponden al mismo receptor.'
                        );
                    }

                    $item = (new FacturaItem())
                        ->setDescripcion(
                            sprintf(
                                'Liquidación #%d - período %s',
                                $liquidacion->getId(),
                                $liquidacion->getPeriodo(),
                            )
                        )
                        ->setMonto($liquidacion->getTotal());

                    $factura->addItem($item);
                    $liquidacion->setFactura($factura);
                }

                $factura->setArchivoPlano(
                    $this->archivoPlanoGenerator->generate($factura)
                );

                $this->entityManager->persist($factura);
                $this->entityManager->flush();

                return $factura;
            }
        );
    }

    private function validatePeriodo(string $periodo): void
    {
        if (
            preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo) !== 1
        ) {
            throw new \InvalidArgumentException(
                'El período debe tener formato YYYY-MM.'
            );
        }
    }
}
