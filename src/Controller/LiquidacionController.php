<?php

namespace App\Controller;

use App\Repository\LiquidacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LiquidacionController extends AbstractController
{
    /**
     * Listado de liquidaciones.
     * Ruta: GET/ANY /liquidaciones
     * Nombre: app_liquidacion_index
     */
    #[Route('/liquidaciones', name: 'app_liquidacion_index')]
    public function index(LiquidacionRepository $liquidacionRepository): Response
    {
        // Trae todas las liquidaciones ordenadas por id ascendente
        $liquidaciones = $liquidacionRepository->findBy([], ['id' => 'ASC']);

        // Pasa la lista a la vista Twig
        return $this->render('liquidacion/index.html.twig', [
            'liquidaciones' => $liquidaciones,
        ]);
    }

    /**
     * Detalle de una liquidación (propiedad, estado, total e ítems).
     * Ruta: GET/ANY /liquidaciones/{id}
     * Nombre: app_liquidacion_show
     * requirements id=\d+ → solo acepta números en {id}
     */
    #[Route('/liquidaciones/{id}', name: 'app_liquidacion_show', requirements: ['id' => '\d+'])]
    public function show(int $id, LiquidacionRepository $liquidacionRepository): Response
    {
        $liquidacion = $liquidacionRepository->find($id);

        // Validación: si no existe, responde 404
        if (!$liquidacion) {
            throw $this->createNotFoundException('Liquidación no encontrada');
        }

        return $this->render('liquidacion/show.html.twig', [
            'liquidacion' => $liquidacion,
        ]);
    }

    /**
     * Marca una liquidación como pagada (draft → paid).
     * Ruta: POST /liquidaciones/{id}/pagar
     * Nombre: app_liquidacion_pagar
     * methods POST → solo acepta formulario POST (no GET)
     * requirements id=\d+ → solo acepta números en {id}
     */
    #[Route('/liquidaciones/{id}/pagar', name: 'app_liquidacion_pagar', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function pagar(
        int $id,
        Request $request,
        LiquidacionRepository $liquidacionRepository,
        EntityManagerInterface $em
    ): Response {
        $liquidacion = $liquidacionRepository->find($id);

        // Validación: liquidación debe existir
        if (!$liquidacion) {
            throw $this->createNotFoundException('Liquidación no encontrada');
        }

        // Validación CSRF: el token del formulario debe coincidir (evita requests falsos)
        if (!$this->isCsrfTokenValid('pagar'.$id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido');
        }

        // Validación de negocio: si ya tiene factura, no se puede pagar de nuevo
        if ($liquidacion->isFacturada()) {
            $this->addFlash('error', 'No se puede pagar: ya está facturada.');
            return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
        }

        // Validación de negocio: solo se paga si está en draft
        if (!$liquidacion->isDraft()) {
            $this->addFlash('error', 'Solo se pueden pagar liquidaciones en estado draft.');
            return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
        }

        // Acción: cambia estado y guarda en BD
        $liquidacion->setEstado('paid');
        $em->flush();

        // Mensaje flash de éxito y vuelve al detalle
        $this->addFlash('success', 'Liquidación pagada.');
        return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
    }
}
