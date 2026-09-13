<?php

namespace App\DataFixtures;

use App\Entity\Arrendatario;
use App\Entity\Empresa;
use App\Entity\Liquidacion;
use App\Entity\LiquidacionItem;
use App\Entity\Propiedad;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1 empresa (emisor)
        $empresa = (new Empresa())
            ->setRut('11111111-1')
            ->setRazonSocial('Proptech Demo SpA')
            ->setGiro('Administracion de arriendos')
            ->setDireccion('Av. Providencia 1234')
            ->setComuna('Providencia')
            ->setCiudad('Santiago')
            ->setEmail('facturacion@proptech-demo.cl');
        $manager->persist($empresa);

        // 1 arrendatario (receptor de la factura)
        $arrendatario = (new Arrendatario())
            ->setRut('12262532-K')
            ->setNombre('Juan Perez Soto')
            ->setGiro('Particular')
            ->setDireccion('Los Leones 500')
            ->setComuna('Providencia')
            ->setCiudad('Santiago')
            ->setEmail('juan.perez@correo.cl');
        $manager->persist($arrendatario);

        // 1 propiedad
        $propiedad = (new Propiedad())
            ->setNombre('Depto Centro 801')
            ->setDireccion('Huérfanos 1200, depto 801')
            ->setEmpresa($empresa)
            ->setArrendatario($arrendatario);
        $manager->persist($propiedad);

        // Liquidación 1 (paid) - mismo período
        $liq1 = (new Liquidacion())
            ->setPropiedad($propiedad)
            ->setPeriodo('2026-03')
            ->setEstado('paid');

        $liq1->addItem(
            (new LiquidacionItem())
                ->setTipo('cargo')
                ->setDescripcion('Arriendo marzo 2026')
                ->setMonto(500000)
        );
        $liq1->addItem(
            (new LiquidacionItem())
                ->setTipo('cargo')
                ->setDescripcion('Gasto comun')
                ->setMonto(50000)
        );
        $liq1->addItem(
            (new LiquidacionItem())
                ->setTipo('descuento')
                ->setDescripcion('Comision administracion')
                ->setMonto(40000)
        );
        $liq1->recalculate(); // 510000
        $manager->persist($liq1);

        // Liquidación 2 (draft) - mismo período
        $liq2 = (new Liquidacion())
            ->setPropiedad($propiedad)
            ->setPeriodo('2026-03')
            ->setEstado('draft');

        $liq2->addItem(
            (new LiquidacionItem())
                ->setTipo('cargo')
                ->setDescripcion('Arriendo marzo 2026')
                ->setMonto(500000)
        );
        $liq2->addItem(
            (new LiquidacionItem())
                ->setTipo('descuento')
                ->setDescripcion('Descuento puntualidad')
                ->setMonto(10000)
        );
        $liq2->recalculate(); // 490000
        $manager->persist($liq2);

        $manager->flush();
    }
}
