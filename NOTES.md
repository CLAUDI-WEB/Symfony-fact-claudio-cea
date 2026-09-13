## Observación para quien revise esto

El uso alto de tokens no viene de “dejar que la IA haga todo el proyecto en un shot”, sino de:

1. Sesiones largas con mucho contexto explicando diferencias entre laravel y sym   
2. Muchos turnos cortos de debugging  
3. la estimacion se da en contexto que tambien estaba haciendo otros proyectos propios , arreglando codigos y trabajando en simultaneo entre 2 proyectos propios

---

## Herramienta

| Ítem | Detalle |
|---|---|
|Ítem--> Herramienta | Detalle-> Cursor Agent |
|Ítem--> Modelo (capturas de usage) |Detalle-> `Grok 5.6` |
|Ítem--> Costo visto en dashboard | Detalle->Included (cupo del plan, sin on-demand en esas filas) |
|Ítem--> Proyecto | Detalle->`facturacion-test-claudio` |

Chats principales usados:

1. **Mini-módulo de facturación** — Etapa 1 (entidades, migración, fixtures, README)
2. **Guía Arrendatario / LiquidacionController** — listado, detalle, pagar, tooling
3. **Nueva migración / generador** — fixtures temporales, Etapa 2 facturación, fix Turbo

---

## Cómo trabajé con la IA

Trabajo principalmente **paso a paso**: pido guía, ejecuto yo los comandos Docker/Maker, pego salidas o errores.

La IA actúa como tutor + debugger (explica conceptos, sugiere código, corrige errores). Yo decido el ritmo, corro la app en el navegador y valido en BD.

---

## Prompts usados (resumen por etapa)

### Etapa 1 — Modelo, migración, fixtures, README

| Prompt (resumen / cita) | Para qué |
|---|---|
| «explicame la estructura de symfony
| «crea la nueva modifica directamente esta vez , si debes crear la migracion nueva hazlo» | Edición directa + migración solo si hace falta estructura |
| «antes de la segunda etapa con los controller , Cierre Etapa 1 + documentación 

**Aprendizaje clave documentado con IA

### Etapa 2a — LiquidacionController

| Prompt (resumen / cita) | Para qué |
|---|---|
| «guiame para ir creando LiquidacionController» | Scaffold + listado |
| «que es lo que hace ese controlador» | Entender responsabilidad |
| «dame un listado de todos los comando para crear archivos …» / «si dejalo en README» | Cheat-sheet Maker |
| «podemos ver la vista de liquidaciones … en el navegador» | Probar UI |
| «y mostrar la data , o aun no tenemos data ?» | Confirmar fixtures vs vista |
| «si dime el paso a paso y lo voy ejecutando yo» | Flujo DIY guiado |
| Pegados de errores (`LiquidacionRepository` mal importado, ruta `pagar`, `Request` sin `use`) | Debugging |
| «sigamos con pagar» | Acción draft → paid |
| «como veo la bd en alguna aplicacion , para veolver el que pagamos a draft ?» | DBeaver / SQL |
| «comenta que hace cada funcion … en el liquidacioncontroller» | Comentarios didácticos |
| «que hace $em->flush();» | Concepto Doctrine |

### Etapa 2b — Generador de facturación

| Prompt (resumen / cita) | Para qué |
|---|---|
| Pegado de la guía «Etapa 2 — Generador de facturación» (Pasos 0–10: Repository, `ArchivoPlanoGenerator`, `FacturacionService`, form, controller, Twig, prueba manual) | Implementar preview → factura → archivo plano |
| «cuando llego al paso http://localhost:8080/facturacion/nueva … en la vista no veo nada» + error Turbo `Form responses must redirect to another location` | Fix: `data-turbo="false"` en el form de preview |
| «recuerdas lo que te estoy pidiendo de la neuva migracion ?» / «si» / «como ejcuto para pasar los datos neuvos a la bd ?» | Liquidaciones temporales ene/feb en fixtures + `doctrine:fixtures:load` |

### Soporte / meta

| Prompt | Para qué |
|---|---|
| «puedes hacer un calculo de los tokens usados hasta el momento ?» | Estimación previa a dashboard |
| Captura del panel Usage de Cursor («me sale esto») | Suma real de requests |
| «cual es el limite de cursor» | Entender plan / pools |
| Prettier / formato Twig | Tooling del editor |
| «necesito documentar los promt usados…» (+ instrucción del enunciado) | Este archivo |

---

## Estimación de tokens

### Fuente primaria — dashboard Cursor (ventana del 10 Sep 2026)

Filas visibles sumadas en chat

| Hora (UTC) | Tokens |
|---|---|
| 10:06 PM | 82.100 |
| 10:03 PM | 81.500 |
| 10:02 PM | 241.800 |
| 10:01 PM | 79.200 |
| 09:59 PM | 79.000 |
| 09:57 PM | 154.600 |
| 09:52 PM | 76.500 |
| 09:50 PM | 76.100 |
| 09:49 PM | 75.500 |
| 09:46 PM | 149.600 |
| 09:43 PM | 145.000 |

**Subtotal de esa ventana ≈ 1.240.900 tokens (~1,24 M).**  
Costo mostrado: **Included en plan**.

Notas:

- Los picos (~145k–242k) coinciden con turnos con más contexto/herramientas.
- El resto ronda ~75k–82k por mensaje en esa sesión.
- Esto **no** es el total de todo el proyecto (uso acumulado buscando otros datos de otros proyectos por ello es una estimacion evaluando lo que dice el uso por fechas.
---

## Qué hice yo vs qué hizo la IA

| Yo | IA (Cursor) |
|---|---|
| Correr `docker compose`, Maker, migrate, fixtures | Explicar qué comando usar y por qué |
| encontrar errores / screenshots / Network + consola | Diagnosticar (p. ej. Turbo vs redirect) |
| Probar en navegador y DBeaver | (Proponer) código de controllers, services, Twig |
| Mantener README / este NOTES |  actualizar documentación |

---
