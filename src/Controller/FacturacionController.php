<?php

namespace App\Controller;

use App\Entity\Factura;
use App\Form\FacturacionPeriodoType;
use App\Service\FacturacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller del generador de facturación.
 * Prefijo de rutas: /facturacion
 */
#[Route('/facturacion')]
class FacturacionController extends AbstractController
{
    /**
     * Pantalla del período + preview de liquidaciones a facturar.
     * Ruta: GET/POST /facturacion/nueva
     * Nombre: facturacion_nueva
     * GET  → muestra el formulario vacío
     * POST → calcula preview si el período es válido
     */
    #[Route('/nueva', name: 'facturacion_nueva', methods: ['GET', 'POST'])]
    public function nueva(
        Request $request, // Datos HTTP de la petición (GET/POST)
        FacturacionService $facturacionService, // Lógica de preview/crear (inyectada)
    ): Response {
        // Crea el formulario del período (campo YYYY-MM)
        $form = $this->createForm(FacturacionPeriodoType::class);

        // Si vino POST, llena el form con los datos enviados
        $form->handleRequest($request);

        // Por defecto no hay preview (primera visita GET)
        $preview = null;

        // Solo si el usuario envió el form y pasó validaciones (NotBlank, Regex)
        if ($form->isSubmitted() && $form->isValid()) {
            // Lee el período tipado como string, ej. "2026-03"
            $periodo = (string) $form->get('periodo')->getData();

            try {
                // Consulta liquidaciones paid sin factura y calcula neto/IVA/total
                $preview = $facturacionService->preview($periodo);
            } catch (\DomainException|\InvalidArgumentException $exception) {
                // Sin pendientes o período inválido → mensaje flash de error
                $this->addFlash('error', $exception->getMessage());
            }
        }

        // Renderiza la vista con el form y el preview (null o array)
        return $this->render('facturacion/nueva.html.twig', [
            'form' => $form,
            'preview' => $preview,
        ]);
    }

    /**
     * Confirma el preview y crea la factura en BD.
     * Ruta: POST /facturacion/confirmar
     * Nombre: facturacion_confirmar
     * Solo POST (no es una vista; redirige al show o de vuelta a nueva)
     */
    #[Route(
        '/confirmar',
        name: 'facturacion_confirmar',
        methods: ['POST'],
    )]
    public function confirmar(
        Request $request,
        FacturacionService $facturacionService,
    ): Response {
        // Período oculto enviado desde el form de confirmación del Twig
        $periodo = (string) $request->request->get('periodo');

        // Valida token CSRF para evitar envíos falsos desde otra página
        if (
            !$this->isCsrfTokenValid(
                'facturar_'.$periodo, // Debe coincidir con csrf_token('facturar_' ~ periodo) del Twig
                (string) $request->request->get('_token'),
            )
        ) {
            // Token inválido → acceso denegado (403)
            throw $this->createAccessDeniedException(
                'Token CSRF inválido.'
            );
        }

        try {
            // Crea Factura + FacturaItem, asocia liquidaciones y genera archivo plano
            $factura = $facturacionService->crear($periodo);

            // Mensaje de éxito con el folio asignado
            $this->addFlash(
                'success',
                sprintf(
                    'Factura #%d creada correctamente.',
                    $factura->getFolio(),
                )
            );

            // PRG: redirect al detalle de la factura creada
            return $this->redirectToRoute('facturacion_show', [
                'id' => $factura->getId(),
            ]);
        } catch (\DomainException|\InvalidArgumentException $exception) {
            // Falló la creación (ej. ya no hay pendientes) → flash y vuelve al form
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('facturacion_nueva');
        }
    }

    /**
     * Detalle de una factura ya creada (snapshot + link de descarga).
     * Ruta: GET /facturacion/{id}
     * Nombre: facturacion_show
     * requirements id=\d+ → solo números
     * ParamConverter: Symfony carga la Factura por {id} o responde 404
     */
    #[Route(
        '/{id}',
        name: 'facturacion_show',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function show(Factura $factura): Response
    {
        // Pasa la entidad a la vista Twig
        return $this->render('facturacion/show.html.twig', [
            'factura' => $factura,
        ]);
    }

    /**
     * Descarga el archivo plano .txt guardado en la factura.
     * Ruta: GET /facturacion/{id}/archivo
     * Nombre: facturacion_archivo
     * No regenera el texto: lee el campo ya persistido
     */
    #[Route(
        '/{id}/archivo',
        name: 'facturacion_archivo',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function descargar(Factura $factura): Response
    {
        // Respuesta HTTP con el contenido del archivo plano
        return new Response(
            $factura->getArchivoPlano(), // Cuerpo: texto Encabezado/Totales/Detalle
            Response::HTTP_OK, // Status 200
            [
                // Indica al navegador que es texto plano
                'Content-Type' => 'text/plain; charset=UTF-8',
                // Fuerza descarga con nombre factura-{folio}.txt
                'Content-Disposition' => sprintf(
                    'attachment; filename="factura-%d.txt"',
                    $factura->getFolio(),
                ),
            ]
        );
    }
}
