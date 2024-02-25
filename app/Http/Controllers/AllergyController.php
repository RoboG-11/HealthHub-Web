<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllergyRequest;
use App\Http\Resources\AllergyResource;
use App\Models\Allergy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AllergyController extends Controller
{
    /**
     * Obtiene una lista paginada de alergias.
     *
     * @OA\Get(
     *     path="/api/allergies",
     *     summary="Obtiene una lista paginada de alergias",
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de alergias",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AllergyResource")),
     *             @OA\Property(property="links", type="object", @OA\Property(property="first", type="string"), @OA\Property(property="last", type="string"), @OA\Property(property="prev", type="string"), @OA\Property(property="next", type="string")),
     *             @OA\Property(property="meta", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="from", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="links", type="array", @OA\Items(type="string")), @OA\Property(property="path", type="string"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="to", type="integer"), @OA\Property(property="total", type="integer"))
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
            $allergies = Allergy::paginate(5);
            $allergies->getCollection()->transform(function ($allergy) {
                return new AllergyResource($allergy);
            });

            $pagination = [
                'success' => true,
                'data' => $allergies->items(),
                'links' => [
                    'first' => $allergies->url(1),
                    'last' => $allergies->url($allergies->lastPage()),
                    'prev' => $allergies->previousPageUrl(),
                    'next' => $allergies->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $allergies->currentPage(),
                    'from' => $allergies->firstItem(),
                    'last_page' => $allergies->lastPage(),
                    'links' => $allergies->getUrlRange(1, $allergies->lastPage()),
                    'path' => $allergies->url(1),
                    'per_page' => $allergies->perPage(),
                    'to' => $allergies->lastItem(),
                    'total' => $allergies->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las alergias: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva alergia.
     *
     * @OA\Post(
     *     path="/api/allergies",
     *     summary="Crea una nueva alergia",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la alergia a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="allergy_name", type="string", example="Peanuts"),
     *             @OA\Property(property="description", type="string", example="Allergy to peanuts")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Alergia creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AllergyResource")
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
     * @param  AllergyRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AllergyRequest $request): JsonResponse
    {
        try {
            $allergy = Allergy::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new AllergyResource($allergy)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva alergia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    /**
     * Muestra una alergia específica.
     *
     * @OA\Get(
     *     path="/api/allergies/{id}",
     *     summary="Muestra una alergia específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alergia a mostrar",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alergia encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AllergyResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alergia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Allergy not found")
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
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $allergy = Allergy::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new AllergyResource($allergy)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allergy not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una alergia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una alergia existente.
     *
     * @OA\Put(
     *     path="/api/allergies/{id}",
     *     summary="Actualiza una alergia existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la alergia",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la alergia a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="allergy_name", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alergia actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AllergyResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alergia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Allergy not found")
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
     * @param AllergyRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AllergyRequest $request, string $id): JsonResponse
    {
        try {
            $allergy = Allergy::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $allergy->fill($request->only([
                'allergy_name', 'description',
            ]));

            $allergy->save();

            return response()->json([
                'success' => true,
                'data' => new AllergyResource($allergy)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allergy not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una alergia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una alergia existente.
     *
     * @OA\Delete(
     *     path="/api/allergies/{id}",
     *     summary="Elimina una alergia existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la alergia a eliminar",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alergia eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alergia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Allergy not found")
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $allergy = Allergy::findOrFail($id);
            $allergy->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allergy not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una alergia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
