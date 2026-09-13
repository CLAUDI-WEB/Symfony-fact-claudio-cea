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
        $form = $this->createForm(FacturacionPeriodoType::class);

        $form->handleRequest($request);

        $preview = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $periodo = (string) $form->get('periodo')->getData();

            try {
                $preview = $facturacionService->preview($periodo);
            } catch (\DomainException|\InvalidArgumentException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

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

        $periodo = (string) $request->request->get('periodo');

        if (
            !$this->isCsrfTokenValid(
                'facturar_'.$periodo,
                (string) $request->request->get('_token'),
            )
        ) {
            // Token inválido → acceso denegado (403)
            throw $this->createAccessDeniedException(
                'Token CSRF inválido.'
            );
        }

        try {
            $factura = $facturacionService->crear($periodo);

            $this->addFlash(
                'success',
                sprintf(
                    'Factura #%d creada correctamente.',
                    $factura->getFolio(),
                )
            );

            return $this->redirectToRoute('facturacion_show', [
                'id' => $factura->getId(),
            ]);
        } catch (\DomainException|\InvalidArgumentException $exception) {
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

        return new Response(
            $factura->getArchivoPlano(),
            Response::HTTP_OK, // Status 200
            [

                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => sprintf(
                    'attachment; filename="factura-%d.txt"',
                    $factura->getFolio(),
                ),
            ]
        );
    }
}
