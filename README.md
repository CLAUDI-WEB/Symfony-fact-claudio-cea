## Supuestos

- La empresa (proptech) es el emisor del DTE: factura lo cobrado por arriendos/liquidaciones.
- El receptor de la factura es el arrendatario (persona natural), no el arrendador/dueño.
- El arrendador puede modelarse como dueño de la propiedad, pero no recibe el documento tributario.
- Solo se factura lo pagado (`paid`) y aún no asociado a una factura.
- “Pagar” solo cambia el estado de la liquidación; no hay pasarela de pago.
- Moneda: CLP en enteros (sin decimales).
- Documento: DTE tipo 33 con IVA 19%.
- `iva = round(neto * 0.19)`; `total = neto + iva`.
- Período de facturación: `YYYY-MM`.
- Estados: `draft` (editable), `paid` (facturable), facturada (bloqueada).
- Alcance de datos: 1 empresa, 1 propiedad, 2 liquidaciones.
- Autenticación fuera de alcance.
