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
        $liquidaciones = $liquidacionRepository->findBy([], ['id' => 'ASC']);

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
    public function pay(
        int $id,
        Request $request,
        LiquidacionRepository $liquidacionRepository,
        EntityManagerInterface $em
    ): Response {
        $liquidacion = $liquidacionRepository->find($id);

        if (!$liquidacion) {
            throw $this->createNotFoundException('Liquidación no encontrada');
        }

        if (!$this->isCsrfTokenValid('pagar'.$id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido');
        }

        if ($liquidacion->isFacturada()) {
            $this->addFlash('error', 'No se puede pagar: ya está facturada.');
            return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
        }

        if (!$liquidacion->isDraft()) {
            $this->addFlash('error', 'Solo se pueden pagar liquidaciones en estado draft.');
            return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
        }

        $liquidacion->setEstado('paid');
        $em->flush();

        $this->addFlash('success', 'Liquidación pagada.');
        return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
    }

    /**
     * Muestra formulario manual para editar liquidación + ítems.
     * Ruta: GET /liquidaciones/modify/{id}
     * Nombre: app_liquidacion_modify_form
     */
    #[Route('/liquidaciones/modify/{id}', name: 'app_liquidacion_modify_form', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function update(int $id, LiquidacionRepository $liquidacionRepository): Response
    {
        $liquidacion = $liquidacionRepository->find($id);

        if (!$liquidacion) {
            throw $this->createNotFoundException('Liquidación no encontrada');
        }

        return $this->render('liquidacion/mod.html.twig', [
            'liquidacion' => $liquidacion,
        ]);
    }

    /**
     * Guarda cambios enviados por el formulario manual.
     * Ruta: POST /liquidaciones/{id}/modificar
     * Nombre: app_liquidacion_modify
     */
    #[Route('/liquidaciones/{id}/modificar', name: 'app_liquidacion_modify', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function modifypost(
        int $id,
        Request $request,
        LiquidacionRepository $liquidacionRepository,
        EntityManagerInterface $em,
    ): Response {
        $liquidacion = $liquidacionRepository->find($id);

        if (!$liquidacion) {
            throw $this->createNotFoundException('Liquidación no encontrada');
        }

        if ($liquidacion->isFacturada()) {
            $this->addFlash('error', 'No se puede editar: ya está facturada.');

            return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
        }

        if (!$this->isCsrfTokenValid('modificar'.$id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido');
        }

        $liquidacion->setPeriodo((string) $request->request->get('periodo'));
        $liquidacion->setEstado((string) $request->request->get('estado'));

        $itemsData = $request->request->all('items');

        foreach ($liquidacion->getItems() as $index => $item) {
            if (!isset($itemsData[$index])) {
                continue;
            }

            $row = $itemsData[$index];
            $item->setTipo((string) ($row['tipo'] ?? $item->getTipo()));
            $item->setDescripcion((string) ($row['descripcion'] ?? $item->getDescripcion()));
            $item->setMonto((int) ($row['monto'] ?? $item->getMonto()));
        }

        $liquidacion->recalculate();
        $em->flush();

        $this->addFlash('success', 'Liquidación actualizada.');

        return $this->redirectToRoute('app_liquidacion_show', ['id' => $id]);
    }
}
