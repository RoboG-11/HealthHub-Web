<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstablishmentRequest;
use App\Http\Resources\EstablishmentResource;
use App\Models\Establishment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstablishmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/establishments",
     *     summary="Obtiene una lista paginada de los establecimientos",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de establecimientos obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EstablishmentResource")),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
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
     */
    public function index(): JsonResponse
    {
        try {
            $establishments = Establishment::paginate(5);
            $establishments->getCollection()->transform(function ($establishment) {
                return new EstablishmentResource($establishment);
            });

            $pagination = [
                'success' => true,
                'data' => $establishments->items(),
                'links' => [
                    'first' => $establishments->url(1),
                    'last' => $establishments->url($establishments->lastPage()),
                    'prev' => $establishments->previousPageUrl(),
                    'next' => $establishments->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $establishments->currentPage(),
                    'from' => $establishments->firstItem(),
                    'last_page' => $establishments->lastPage(),
                    'links' => $establishments->getUrlRange(1, $establishments->lastPage()),
                    'path' => $establishments->url(1),
                    'per_page' => $establishments->perPage(),
                    'to' => $establishments->lastItem(),
                    'total' => $establishments->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los establecimientos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea un nuevo establecimiento.
     *
     * @OA\Post(
     *     path="/api/establishments",
     *     summary="Crea un nuevo establecimiento",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del establecimiento a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Establecimiento"),
     *             @OA\Property(property="address", type="string", example="Dirección"),
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="email", type="string", example="info@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Establecimiento creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/EstablishmentResource")
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
     * @param EstablishmentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EstablishmentRequest $request): JsonResponse
    {
        try {
            $establishment = Establishment::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $establishment
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear un nuevo establecimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    /**
     * Muestra un establecimiento específico.
     *
     * @OA\Get(
     *     path="/api/establishments/{id}",
     *     summary="Muestra un establecimiento específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del establecimiento a mostrar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Establecimiento encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/EstablishmentResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Establecimiento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Establecimiento no encontrado")
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
            $establishment = Establishment::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new EstablishmentResource($establishment)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Establishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener un establishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza un establecimiento existente.
     *
     * @OA\Put(
     *     path="/api/establishments/{id}",
     *     summary="Actualiza un establecimiento existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del establecimiento a actualizar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del establecimiento a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="establishment_name", type="string", example="Nuevo nombre"),
     *             @OA\Property(property="establishment_type", type="string", example="Nuevo tipo"),
     *             @OA\Property(property="website_url", type="string", example="https://nuevositio.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Establecimiento actualizado correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/EstablishmentResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Establecimiento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Establecimiento no encontrado")
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
     * @param EstablishmentRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(EstablishmentRequest $request, string $id): JsonResponse
    {
        try {
            $establishment = Establishment::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $establishment->fill($request->only([
                'establishment_name', 'establishment_type', 'website_url',
            ]));

            $establishment->save();

            return response()->json([
                'success' => true,
                'data' => new EstablishmentResource($establishment)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Establishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar un establishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina un establecimiento existente.
     *
     * @OA\Delete(
     *     path="/api/establishments/{id}",
     *     summary="Elimina un establecimiento existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del establecimiento a eliminar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Establecimiento eliminado correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Establecimiento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Establecimiento no encontrado")
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
            $establishment = Establishment::findOrFail($id);
            $establishment->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Establishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting establishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
