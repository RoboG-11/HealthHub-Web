<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicationRequest;
use App\Http\Resources\MedicationResource;
use App\Models\Medication;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicationController extends Controller
{

    /**
     * Obtiene una lista paginada de medicaciones.
     *
     * @OA\Get(
     *     path="/api/medications",
     *     summary="Obtiene una lista paginada de medicaciones",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de medicaciones obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="medication_name", type="string", example="Nombre de la medicación"),
     *                 @OA\Property(property="description", type="string", example="Descripción de la medicación")
     *             )),
     *             @OA\Property(property="links", type="object", @OA\Property(property="first", type="string"), @OA\Property(property="last", type="string"), @OA\Property(property="prev", type="string"), @OA\Property(property="next", type="string")),
     *             @OA\Property(property="meta", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="from", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="links", type="string"), @OA\Property(property="path", type="string"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="to", type="integer"), @OA\Property(property="total", type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor.")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $medications = Medication::paginate(5);
            $medications->getCollection()->transform(function ($medication) {
                return [
                    'id' => $medication->id,
                    'medication_name' => $medication->medication_name,
                    'description' => $medication->description,
                ];
            });

            $pagination = [
                'success' => true,
                'data' => $medications->items(),
                'links' => [
                    'first' => $medications->url(1),
                    'last' => $medications->url($medications->lastPage()),
                    'prev' => $medications->previousPageUrl(),
                    'next' => $medications->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $medications->currentPage(),
                    'from' => $medications->firstItem(),
                    'last_page' => $medications->lastPage(),
                    'links' => $medications->getUrlRange(1, $medications->lastPage()),
                    'path' => $medications->url(1),
                    'per_page' => $medications->perPage(),
                    'to' => $medications->lastItem(),
                    'total' => $medications->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las medicaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva medicación.
     *
     * @OA\Post(
     *     path="/api/medications",
     *     summary="Crea una nueva medicación",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la medicación a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="medication_name", type="string", example="Nombre de la medicación"),
     *             @OA\Property(property="description", type="string", example="Descripción de la medicación")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Medicación creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     *
     * @param MedicationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(MedicationRequest $request): JsonResponse
    {
        try {
            $medications = Medication::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new MedicationResource($medications)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva medicación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    /**
     * Obtiene una medicación por su ID.
     *
     * @OA\Get(
     *     path="/api/medications/{id}",
     *     summary="Obtiene una medicación por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medicación a obtener",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medicación obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medicación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Medicación no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $medications = Medication::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new MedicationResource($medications)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Medication not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una Medicación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una medicación existente.
     *
     * @OA\Put(
     *     path="/api/medications/{id}",
     *     summary="Actualiza una medicación existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medicación a actualizar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la medicación a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="medication_name", type="string", example="Nombre de la medicación"),
     *             @OA\Property(property="description", type="string", example="Descripción de la medicación")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medicación actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medicación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Medicación no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     *
     * @param MedicationRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(MedicationRequest $request, string $id): JsonResponse
    {
        try {
            $medications = Medication::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $medications->fill($request->only([
                'medication_name', 'description',
            ]));

            $medications->save();

            return response()->json([
                'success' => true,
                'data' => new MedicationResource($medications)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'medicationn not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una medicación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una medicación existente.
     *
     * @OA\Delete(
     *     path="/api/medications/{id}",
     *     summary="Elimina una medicación existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la medicación a eliminar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medicación eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medicación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Medicación no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $medications = Medication::findOrFail($id);
            $medications->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'medication not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una medicación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
