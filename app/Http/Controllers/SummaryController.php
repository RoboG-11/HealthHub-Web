<?php

namespace App\Http\Controllers;

use App\Http\Requests\SummaryRequest;
use App\Http\Resources\SummaryResource;
use App\Models\Summary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SummaryController extends Controller
{
    /**
     * Obtiene una lista paginada de resúmenes.
     *
     * @OA\Get(
     *     path="/api/summaries",
     *     summary="Obtiene una lista paginada de resúmenes",
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de resúmenes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SummaryResource")
     *             ),
     *             @OA\Property(property="links", type="object", example="..."),
     *             @OA\Property(property="meta", type="object", example="...")
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $summaries = Summary::paginate(5);
            $summaries->getCollection()->transform(function ($summary) {
                return new SummaryResource($summary);
            });

            $pagination = [
                'success' => true,
                'data' => $summaries->items(),
                'links' => [
                    'first' => $summaries->url(1),
                    'last' => $summaries->url($summaries->lastPage()),
                    'prev' => $summaries->previousPageUrl(),
                    'next' => $summaries->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $summaries->currentPage(),
                    'from' => $summaries->firstItem(),
                    'last_page' => $summaries->lastPage(),
                    'links' => $summaries->getUrlRange(1, $summaries->lastPage()),
                    'path' => $summaries->url(1),
                    'per_page' => $summaries->perPage(),
                    'to' => $summaries->lastItem(),
                    'total' => $summaries->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los resúmenes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/summaries",
     *     summary="Crea un nuevo resumen",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del resumen a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="appointment_id", type="integer", example="1"),
     *             @OA\Property(property="diagnosis", type="string", example="Diagnosis details"),
     *             @OA\Property(property="medicines", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="name", type="string", example="Medicine Name"),
     *                 @OA\Property(property="dosage", type="string", example="Dosage details"),
     *                 @OA\Property(property="frequency", type="string", example="Frequency details"),
     *                 @OA\Property(property="duration", type="string", example="Duration details"),
     *                 @OA\Property(property="notes", type="string", example="Additional notes")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resumen creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/SummaryResource")
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
     * @param SummaryRequest $request
     * @return JsonResponse
     */
    public function store(SummaryRequest $request): JsonResponse
    {
        try {
            $summary = Summary::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => new SummaryResource($summary)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear un nuevo resumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/summaries/{id}",
     *     summary="Obtiene un resumen por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del resumen a obtener",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resumen obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/SummaryResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resumen no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Summary not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $summary = Summary::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new SummaryResource($summary)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Summary not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/summaries/{id}",
     *     summary="Actualiza un resumen existente por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del resumen a actualizar",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del resumen a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="appointment_id", type="integer", example="1"),
     *             @OA\Property(property="diagnosis", type="string", example="Some diagnosis"),
     *             @OA\Property(property="medicines", type="string", example="Medicine name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resumen actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/SummaryResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resumen no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Summary not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return JsonResponse
     */
    public function update(SummaryRequest $request, string $id): JsonResponse
    {
        try {
            $summary = Summary::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $summary->fill($request->validated());
            $summary->save();

            return response()->json([
                'success' => true,
                'data' => new SummaryResource($summary)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Summary not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/summaries/{id}",
     *     summary="Elimina un resumen por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del resumen a eliminar",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resumen eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resumen no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Summary not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $summary = Summary::findOrFail($id);
            $summary->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Summary not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
