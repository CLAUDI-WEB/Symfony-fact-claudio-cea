<?php

namespace App\Repository;

use App\Entity\Liquidacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Liquidacion>
 */
class LiquidacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Liquidacion::class);
    }

    public function findPendientesFacturacion(string $periodo): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.periodo = :periodo')
            ->andWhere('l.estado = :estado')
            ->andWhere('l.factura IS NULL')
            ->setParameter('periodo', $periodo)
            ->setParameter('estado', 'paid')
            ->setParameter('estado', 'draft')
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
