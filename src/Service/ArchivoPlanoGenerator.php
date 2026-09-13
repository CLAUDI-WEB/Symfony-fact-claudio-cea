<?php

namespace App\Service;

use App\Entity\Factura;

class ArchivoPlanoGenerator
{
    public function generate(Factura $factura): string
    {
        $receptor = $factura->getArrendatario();

        if ($receptor === null) {
            throw new \DomainException('La factura no tiene receptor.');
        }

        $lines = [
            '->Encabezado<-',
            sprintf(
                '%d;%d;%s;0;0;%s;%s;%s;%s;%s;%s;%s;',
                $factura->getTipoDte(),
                $factura->getFolio(),
                $factura->getFechaEmision()?->format('Y-m-d'),
                $this->clean($receptor->getRut()),
                $this->clean($receptor->getNombre(), 100),
                $this->clean($receptor->getGiro(), 40),
                $this->clean($receptor->getDireccion(), 60),
                $this->clean($receptor->getComuna(), 20),
                $this->clean($receptor->getCiudad(), 20),
                $this->clean($receptor->getEmail(), 200),
            ),
            '->Totales<-',
            sprintf(
                '0;0;0;0;%d;%d;19;%d;%d;',
                $factura->getNeto(),
                $factura->getExento(),
                $factura->getIva(),
                $factura->getTotal(),
            ),
            '->Detalle<-',
        ];

        foreach ($factura->getItems() as $index => $item) {
            $number = $index + 1;

            $lines[] = sprintf(
                '%d;LIQ-%03d;%s;1;%d;0;0;0;0;0;%d;INT1;UN;;',
                $number,
                $number,
                $this->clean($item->getDescripcion(), 80),
                $item->getMonto(),
                $item->getMonto(),
            );
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function clean(?string $value, int $maxLength = 200): string
    {
        $value = str_replace(
            [';', "\r", "\n"],
            [',', ' ', ' '],
            trim((string) $value),
        );

        return mb_substr($value, 0, $maxLength);
    }
}
