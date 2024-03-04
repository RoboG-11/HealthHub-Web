<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorSpecialtyRequest;
use App\Http\Resources\DoctorSpecialtyResource;
use App\Models\DoctorSpecialty;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DoctorSpecialtyController extends Controller
{
    /**
     * Obtiene una lista paginada de las especialidades de los doctores.
     *
     * @OA\Get(
     *     path="/api/doctor-specialties",
     *     summary="Obtiene una lista paginada de las especialidades de los doctores",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de especialidades de los doctores obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DoctorSpecialtyResource")),
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
     *                 @OA\Property(property="links", type="array", @OA\Items()),
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
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(): JsonResponse
    {
        try {
            $doctorId = Auth::user()->doctor->user_id;
            $doctorSpecialties = DoctorSpecialty::where('doctor_user_id', $doctorId)->paginate(5);
            $doctorSpecialties->getCollection()->transform(function ($doctorSpecialty) {
                return new DoctorSpecialtyResource($doctorSpecialty);
            });

            $pagination = [
                'success' => true,
                'data' => $doctorSpecialties->items(),
                'links' => [
                    'first' => $doctorSpecialties->url(1),
                    'last' => $doctorSpecialties->url($doctorSpecialties->lastPage()),
                    'prev' => $doctorSpecialties->previousPageUrl(),
                    'next' => $doctorSpecialties->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $doctorSpecialties->currentPage(),
                    'from' => $doctorSpecialties->firstItem(),
                    'last_page' => $doctorSpecialties->lastPage(),
                    'links' => $doctorSpecialties->getUrlRange(1, $doctorSpecialties->lastPage()),
                    'path' => $doctorSpecialties->url(1),
                    'per_page' => $doctorSpecialties->perPage(),
                    'to' => $doctorSpecialties->lastItem(),
                    'total' => $doctorSpecialties->total(),
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


    // public function index(): JsonResponse
    // {
    //     try {
    //         $doctorId = Auth::user()->doctor->user_id;
    //         $doctorSpecialties = DoctorSpecialty::where('doctor_user_id', $doctorId)->paginate(5);

    //         $pagination = [
    //             'success' => true,
    //             'data' => $doctorSpecialties->items(),
    //             'links' => [
    //                 'first' => $doctorSpecialties->url(1),
    //                 'last' => $doctorSpecialties->url($doctorSpecialties->lastPage()),
    //                 'prev' => $doctorSpecialties->previousPageUrl(),
    //                 'next' => $doctorSpecialties->nextPageUrl(),
    //             ],
    //             'meta' => [
    //                 'current_page' => $doctorSpecialties->currentPage(),
    //                 'from' => $doctorSpecialties->firstItem(),
    //                 'last_page' => $doctorSpecialties->lastPage(),
    //                 'links' => $doctorSpecialties->getUrlRange(1, $doctorSpecialties->lastPage()),
    //                 'path' => $doctorSpecialties->url(1),
    //                 'per_page' => $doctorSpecialties->perPage(),
    //                 'to' => $doctorSpecialties->lastItem(),
    //                 'total' => $doctorSpecialties->total(),
    //             ],
    //         ];

    //         return response()->json($pagination, 200);
    //     } catch (QueryException $e) {
    //         Log::error('Error en la consulta al obtener todas las especialidades de los doctores: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error interno del servidor'
    //         ], 500);
    //     }
    // }

    // public function index(): JsonResponse
    // {
    //     try {
    //         $doctorSpecialties = DoctorSpecialty::paginate(5);
    //         $doctorSpecialties->getCollection()->transform(function ($doctorSpecialty) {
    //             return new DoctorSpecialtyResource($doctorSpecialty);
    //         });

    //         $pagination = [
    //             'success' => true,
    //             'data' => $doctorSpecialties->items(),
    //             'links' => [
    //                 'first' => $doctorSpecialties->url(1),
    //                 'last' => $doctorSpecialties->url($doctorSpecialties->lastPage()),
    //                 'prev' => $doctorSpecialties->previousPageUrl(),
    //                 'next' => $doctorSpecialties->nextPageUrl(),
    //             ],
    //             'meta' => [
    //                 'current_page' => $doctorSpecialties->currentPage(),
    //                 'from' => $doctorSpecialties->firstItem(),
    //                 'last_page' => $doctorSpecialties->lastPage(),
    //                 'links' => $doctorSpecialties->getUrlRange(1, $doctorSpecialties->lastPage()),
    //                 'path' => $doctorSpecialties->url(1),
    //                 'per_page' => $doctorSpecialties->perPage(),
    //                 'to' => $doctorSpecialties->lastItem(),
    //                 'total' => $doctorSpecialties->total(),
    //             ],
    //         ];

    //         return response()->json($pagination, 200);
    //     } catch (QueryException $e) {
    //         Log::error('Error en la consulta al obtener todas las especialidades de los doctores: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error interno del servidor'
    //         ], 500);
    //     }
    // }

    /**
     * Crea una nueva relación entre doctor y especialidad.
     *
     * @OA\Post(
     *     path="/api/doctor-specialties",
     *     summary="Crea una nueva relación entre doctor y especialidad",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación entre doctor y especialidad a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="doctor_user_id", type="integer", example="1"),
     *             @OA\Property(property="specialty_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Relación entre doctor y especialidad creada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DoctorSpecialtyResource")
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
     * @param DoctorSpecialtyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DoctorSpecialtyRequest $request): JsonResponse
    {
        try {
            $doctor_specialty = DoctorSpecialty::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $doctor_specialty
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva relación entre doctor y especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    /**
     * Obtiene una relación entre doctor y especialidad por su ID.
     *
     * @OA\Get(
     *     path="/api/doctor-specialties/{id}",
     *     summary="Obtiene una relación entre doctor y especialidad por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre doctor y especialidad",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre doctor y especialidad obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DoctorSpecialtyResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre doctor y especialidad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="DoctorSpecialty not found")
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
            $doctor_specialty = DoctorSpecialty::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new DoctorSpecialtyResource($doctor_specialty)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'DoctorSpecialty not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una DoctorSpecialty: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una relación entre doctor y especialidad por su ID.
     *
     * @OA\Put(
     *     path="/api/doctor-specialties/{id}",
     *     summary="Actualiza una relación entre doctor y especialidad por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre doctor y especialidad",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación entre doctor y especialidad a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="doctor_user_id", type="integer", example="1"),
     *             @OA\Property(property="specialty_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre doctor y especialidad actualizada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DoctorSpecialtyResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre doctor y especialidad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="DoctorSpecialty not found")
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
     * @param DoctorSpecialtyRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DoctorSpecialtyRequest $request, string $id): JsonResponse
    {
        try {
            $doctor_specialty = DoctorSpecialty::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $doctor_specialty->fill($request->only([
                'doctor_user_id', 'specialty_id'
            ]));

            $doctor_specialty->save();

            return response()->json([
                'success' => true,
                'data' => new DoctorSpecialtyResource($doctor_specialty)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'DoctorSpecialty not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar un DoctorSpecialty: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una relación entre doctor y especialidad por su ID.
     *
     * @OA\Delete(
     *     path="/api/doctor-specialties/{id}",
     *     summary="Elimina una relación entre doctor y especialidad por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre doctor y especialidad",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre doctor y especialidad eliminada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre doctor y especialidad no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="DoctorSpecialty not found")
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
            $doctor_specialty = DoctorSpecialty::findOrFail($id);
            $doctor_specialty->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'DoctorSpecialty not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar un DoctorSpecialty: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
