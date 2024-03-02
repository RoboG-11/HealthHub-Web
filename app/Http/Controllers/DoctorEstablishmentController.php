<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorEstablishmentRequest;
use App\Http\Resources\DoctorEstablishmentResource;
use App\Models\DoctorEstablishment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorEstablishmentController extends Controller
{
    /**
     * Obtiene una lista paginada de establecimientos de doctores.
     *
     * @OA\Get(
     *     path="/api/doctor-establishments",
     *     summary="Obtiene una lista paginada de establecimientos de doctores",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de establecimientos de doctores obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DoctorEstablishmentResource")),
     *             @OA\Property(property="links", type="object", 
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string"),
     *             ),
     *             @OA\Property(property="meta", type="object", 
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *             ),
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
            $doctorEstablishments = DoctorEstablishment::paginate(5);
            $doctorEstablishments->getCollection()->transform(function ($doctorEstablishment) {
                return new DoctorEstablishmentResource($doctorEstablishment);
            });

            $pagination = [
                'success' => true,
                'data' => $doctorEstablishments->items(),
                'links' => [
                    'first' => $doctorEstablishments->url(1),
                    'last' => $doctorEstablishments->url($doctorEstablishments->lastPage()),
                    'prev' => $doctorEstablishments->previousPageUrl(),
                    'next' => $doctorEstablishments->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $doctorEstablishments->currentPage(),
                    'from' => $doctorEstablishments->firstItem(),
                    'last_page' => $doctorEstablishments->lastPage(),
                    'links' => $doctorEstablishments->getUrlRange(1, $doctorEstablishments->lastPage()),
                    'path' => $doctorEstablishments->url(1),
                    'per_page' => $doctorEstablishments->perPage(),
                    'to' => $doctorEstablishments->lastItem(),
                    'total' => $doctorEstablishments->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las especialidades de los doctores: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva relación entre doctor y establecimiento médico.
     *
     * @OA\Post(
     *     path="/api/doctor-establishments",
     *     summary="Crea una nueva relación entre doctor y establecimiento médico.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación a crear",
     *         @OA\JsonContent(
     *             required={
     *                 "doctor_user_id",
     *                 "establishment_id"
     *             },
     *             @OA\Property(property="doctor_user_id", type="integer"),
     *             @OA\Property(property="establishment_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Relación creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DoctorEstablishmentResource")
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
     * @param DoctorEstablishmentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DoctorEstablishmentRequest $request): JsonResponse
    {
        try {
            $doctorEstablishment = DoctorEstablishment::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $doctorEstablishment
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva relación entre doctor y establecimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    /**
     * Muestra una relación entre doctor y establecimiento médico específica.
     *
     * @OA\Get(
     *     path="/api/doctor-establishments/{id}",
     *     summary="Muestra una relación específica entre doctor y establecimiento médico.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación a mostrar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DoctorEstablishmentResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="DoctorEstablishment no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="DoctorEstablishment not found")
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
            $doctorEstablishment = DoctorEstablishment::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $doctorEstablishment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'DoctorEstablishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener un DoctorEstablishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una relación entre doctor y establecimiento médico.
     *
     * @OA\Put(
     *     path="/api/doctor-establishments/{id}",
     *     summary="Actualiza una relación entre doctor y establecimiento médico.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación a actualizar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación a actualizar",
     *         @OA\JsonContent(
     *             required={
     *                 "doctor_user_id",
     *                 "establishment_id"
     *             },
     *             @OA\Property(property="doctor_user_id", type="integer"),
     *             @OA\Property(property="establishment_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="DoctorEstablishment no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="DoctorEstablishment not found")
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
     * @param \App\Http\Requests\DoctorEstablishmentRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DoctorEstablishmentRequest $request, string $id): JsonResponse
    {
        try {
            $doctorEstablishment = DoctorEstablishment::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $doctorEstablishment->fill($request->only([
                'doctor_user_id', 'establishment_id'
            ]));

            $doctorEstablishment->save();

            return response()->json([
                'success' => true,
                'data' => $doctorEstablishment
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'DoctorEstablishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar un DoctorEstablishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una relación entre doctor y establecimiento médico por su ID.
     *
     * @OA\Delete(
     *     path="/api/doctor-establishments/{id}",
     *     summary="Elimina una relación entre doctor y establecimiento médico por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre doctor y establecimiento médico",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre doctor y establecimiento médico eliminada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre doctor y establecimiento médico no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="DoctorEstablishment not found")
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
            $doctorEstablishment = DoctorEstablishment::findOrFail($id);
            $doctorEstablishment->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'DoctorEstablishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar un DoctorEstablishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
