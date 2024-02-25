<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiseaseRequest;
use App\Http\Resources\DiseaseResource;
use App\Models\Disease;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiseaseController extends Controller
{

    /**
     * Obtiene una lista paginada de todas las enfermedades.
     *
     * @OA\Get(
     *     path="/api/diseases",
     *     summary="Obtiene una lista paginada de todas las enfermedades",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de enfermedades obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="disease_name", type="string", example="Influenza"),
     *                     @OA\Property(property="description", type="string", example="Viral infection that affects the respiratory system.")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example="1"),
     *                 @OA\Property(property="from", type="integer", example="1"),
     *                 @OA\Property(property="last_page", type="integer", example="3"),
     *                 @OA\Property(property="links", type="array",
     *                     @OA\Items(type="string")
     *                 ),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer", example="5"),
     *                 @OA\Property(property="to", type="integer", example="5"),
     *                 @OA\Property(property="total", type="integer", example="13")
     *             )
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $diseases = Disease::paginate(5);
            $diseases->getCollection()->transform(function ($disease) {
                return [
                    'id' => $disease->id,
                    'disease_name' => $disease->disease_name,
                    'description' => $disease->description,
                ];
            });

            $pagination = [
                'success' => true,
                'data' => $diseases->items(),
                'links' => [
                    'first' => $diseases->url(1),
                    'last' => $diseases->url($diseases->lastPage()),
                    'prev' => $diseases->previousPageUrl(),
                    'next' => $diseases->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $diseases->currentPage(),
                    'from' => $diseases->firstItem(),
                    'last_page' => $diseases->lastPage(),
                    'links' => $diseases->getUrlRange(1, $diseases->lastPage()),
                    'path' => $diseases->url(1),
                    'per_page' => $diseases->perPage(),
                    'to' => $diseases->lastItem(),
                    'total' => $diseases->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las enfermedades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva enfermedad.
     *
     * @OA\Post(
     *     path="/api/diseases",
     *     summary="Crea una nueva enfermedad",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la enfermedad a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="disease_name", type="string", example="Influenza"),
     *             @OA\Property(property="description", type="string", example="Viral infection that affects the respiratory system.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enfermedad creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DiseaseResource")
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
     * @param DiseaseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DiseaseRequest $request): JsonResponse
    {
        try {
            $diseases = Disease::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new DiseaseResource($diseases)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva enfermedad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    /**
     * Obtiene una enfermedad específica por su ID.
     *
     * @OA\Get(
     *     path="/api/diseases/{id}",
     *     summary="Obtiene una enfermedad específica por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la enfermedad",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enfermedad obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DiseaseResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enfermedad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Disease not found")
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
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $disease = Disease::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new DiseaseResource($disease)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una enfermedad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una enfermedad existente.
     *
     * @OA\Put(
     *     path="/api/diseases/{id}",
     *     summary="Actualiza una enfermedad existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la enfermedad",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la enfermedad a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="disease_name", type="string", example="Updated Disease Name"),
     *             @OA\Property(property="description", type="string", example="Updated description of the disease.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enfermedad actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DiseaseResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enfermedad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Disease not found")
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
     * @param DiseaseRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DiseaseRequest $request, string $id): JsonResponse
    {
        try {
            $disease = Disease::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $disease->fill($request->only([
                'disease_name', 'description',
            ]));

            $disease->save();

            return response()->json([
                'success' => true,
                'data' => new DiseaseResource($disease)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una enfermedad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una enfermedad existente.
     *
     * @OA\Delete(
     *     path="/api/diseases/{id}",
     *     summary="Elimina una enfermedad existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la enfermedad",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enfermedad eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enfermedad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Disease not found")
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
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $disease = Disease::findOrFail($id);
            $disease->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una enfermedad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
