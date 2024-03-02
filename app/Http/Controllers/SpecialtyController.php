<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use App\Models\Specialty;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpecialtyController extends Controller
{
    /**
     * Obtiene una lista paginada de todas las especialidades médicas.
     *
     * @OA\Get(
     *     path="/api/specialties",
     *     summary="Obtiene una lista paginada de todas las especialidades médicas",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de especialidades médicas obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SpecialtyResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="/api/specialties?page=1"),
     *                 @OA\Property(property="last", type="string", example="/api/specialties?page=last_page"),
     *                 @OA\Property(property="prev", type="string", example="/api/specialties?page=prev_page"),
     *                 @OA\Property(property="next", type="string", example="/api/specialties?page=next_page")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example="current_page"),
     *                 @OA\Property(property="from", type="integer", example="from_item"),
     *                 @OA\Property(property="last_page", type="integer", example="last_page"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="path", type="string", example="/api/specialties"),
     *                 @OA\Property(property="per_page", type="integer", example="per_page"),
     *                 @OA\Property(property="to", type="integer", example="to_item"),
     *                 @OA\Property(property="total", type="integer", example="total_items")
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $specialties = Specialty::paginate(5);
            $specialties->getCollection()->transform(function ($specialty) {
                return new SpecialtyResource($specialty);
            });

            $pagination = [
                'success' => true,
                'data' => $specialties->items(),
                'links' => [
                    'first' => $specialties->url(1),
                    'last' => $specialties->url($specialties->lastPage()),
                    'prev' => $specialties->previousPageUrl(),
                    'next' => $specialties->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $specialties->currentPage(),
                    'from' => $specialties->firstItem(),
                    'last_page' => $specialties->lastPage(),
                    'links' => $specialties->getUrlRange(1, $specialties->lastPage()),
                    'path' => $specialties->url(1),
                    'per_page' => $specialties->perPage(),
                    'to' => $specialties->lastItem(),
                    'total' => $specialties->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las especialidades médicas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva especialidad médica.
     *
     * @OA\Post(
     *     path="/api/specialties",
     *     summary="Crea una nueva especialidad médica",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la especialidad médica a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Cardiología"),
     *             @OA\Property(property="description", type="string", example="Especialidad médica que se encarga del estudio, diagnóstico y tratamiento de las enfermedades del corazón y del aparato circulatorio.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Especialidad médica creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/SpecialtyResource")
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
     * @param SpecialtyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SpecialtyRequest $request): JsonResponse
    {
        try {
            $specialty = Specialty::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new SpecialtyResource($specialty)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Muestra una especialidad médica específica.
     *
     * @OA\Get(
     *     path="/api/specialties/{id}",
     *     summary="Muestra una especialidad médica específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la especialidad médica a mostrar",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Especialidad médica encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/SpecialtyResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Especialidad médica no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Specialty not found")
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
    public function show(string $id): JsonResponse
    {
        try {
            $specialty = Specialty::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new SpecialtyResource($specialty)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una especialidad médica existente.
     *
     * @OA\Put(
     *     path="/api/specialties/{id}",
     *     summary="Actualiza una especialidad médica existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la especialidad médica a actualizar",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la especialidad médica a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="specialty_name", type="string", example="Nueva especialidad"),
     *             @OA\Property(property="description", type="string", example="Descripción de la nueva especialidad")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Especialidad médica actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/SpecialtyResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Especialidad médica no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Specialty not found")
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
     * @param SpecialtyRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(SpecialtyRequest $request, string $id): JsonResponse
    {
        try {
            $specialty = Specialty::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $specialty->fill($request->only([
                'specialty_name', 'description',
            ]));

            $specialty->save();

            return response()->json([
                'success' => true,
                'data' => new SpecialtyResource($specialty)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una especialidad médica existente.
     *
     * @OA\Delete(
     *     path="/api/specialties/{id}",
     *     summary="Elimina una especialidad médica existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la especialidad médica a eliminar",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Especialidad médica eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Especialidad médica no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Specialty not found")
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
            $specialty = Specialty::findOrFail($id);
            $specialty->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
