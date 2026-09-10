# Facturación Proptech — Mini-módulo Symfony
Mini-módulo de facturación manual para liquidaciones de arriendo.
**Stack:** PHP 8.3 + Symfony 7.4 LTS + Doctrine + PostgreSQL 16 + Docker
---
## Etapa 1 — Completada (modelo + BD + fixtures)
### Qué se hizo
- Proyecto Symfony corriendo 100% en Docker (sin PHP instalado en Windows)
- Modelo de datos propio con relaciones
- Migraciones Doctrine aplicadas
- Fixtures de alcance de la prueba cargadas
- Validación de schema OK (`mapping` + `database` in sync)
### Entidades creadas
| Entidad | Rol |
|---|---|
| Empresa | Emisor del DTE (proptech) |
| Arrendatario | Receptor de la factura (persona natural) |
| Propiedad | Inmueble (liga empresa + arrendatario) |
| Liquidacion | Liquidación por período (`draft` / `paid`) |
| LiquidacionItem | Cargos o descuentos (monto positivo) |
| Factura | Documento de facturación (DTE 33 + IVA) |
| FacturaItem | Snapshot de montos al facturar |
### Repositories
- EmpresaRepository
- ArrendatarioRepository
- PropiedadRepository
- LiquidacionRepository
- LiquidacionItemRepository
- FacturaRepository (incluye `getNextFolio()`)
- FacturaItemRepository
### Datos seed (fixtures)
- 1 empresa: RUT `11111111-1` — Proptech Demo SpA
- 1 arrendatario: Juan Perez Soto
- 1 propiedad: Depto Centro 801
- 2 liquidaciones período `2026-03`:
  - id 1 → estado `paid`, total `510000`
  - id 2 → estado `draft`, total `490000`
### Modelo
```text
Empresa 1──<N Propiedad N>──1 Arrendatario
                 │
                 └──1──<N Liquidacion 1──<N LiquidacionItem
                              │
                              └── ManyToOne nullable → Factura
Factura 1──<N FacturaItem (snapshot)
```
---
## Cómo levantar
```bash
docker compose up -d
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php bin/console doctrine:fixtures:load --no-interaction
```
**URL:** http://localhost:8080
---
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
---
## Comandos Maker (crear archivos)

Todos desde la raíz del proyecto, dentro de Docker.

```bash
# Entidad + Repository
docker compose exec php bin/console make:entity Empresa
docker compose exec php bin/console make:entity Arrendatario
docker compose exec php bin/console make:entity Propiedad
docker compose exec php bin/console make:entity Liquidacion
docker compose exec php bin/console make:entity LiquidacionItem
docker compose exec php bin/console make:entity Factura
docker compose exec php bin/console make:entity FacturaItem

# Migración (crea migrations/Version....php)
docker compose exec php bin/console make:migration

# Controller + template Twig
docker compose exec php bin/console make:controller LiquidacionController
# tests → no
# twig template → yes

# Próximos (Etapa 2/3)
docker compose exec php bin/console make:form LiquidacionType
docker compose exec php bin/console make:controller FacturacionController
```

| Comando | Archivos que crea |
|---|---|
| `make:entity X` | `src/Entity/X.php` + `src/Repository/XRepository.php` |
| `make:migration` | `migrations/Version....php` |
| `make:controller X` | `src/Controller/X.php` + `templates/.../index.html.twig` |
| `make:form X` | `src/Form/X.php` |
| `doctrine:fixtures:load` | No crea archivos; carga datos en BD |

---

## Comandos usados en la Etapa 1
```bash
# Ambiente
docker compose up -d
docker compose exec php php -v
docker compose exec php bin/console about

# Entidades / validación
docker compose exec php bin/console make:entity ...
docker compose exec php php -l src/Entity/...
docker compose exec php bin/console doctrine:schema:validate

# Migraciones
docker compose exec php bin/console make:migration
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction

# Fixtures
docker compose exec php bin/console doctrine:fixtures:load --no-interaction
docker compose exec php bin/console dbal:run-sql "SELECT id, periodo, estado, total FROM liquidacion"
```
---
## Etapa 2 — Pendiente
- Controller + Forms + vistas de liquidaciones
  - Listado `/liquidaciones`
  - Detalle/edición `/liquidaciones/{id}`
  - Pagar liquidación
- Recálculo de montos al guardar
- Generador de facturación
  - Período + preview
  - Crear factura
  - No incluir lo ya facturado
- Archivo plano `.txt` (facturacion.cl)
- NOTES.md (prompts de IA + tokens)
