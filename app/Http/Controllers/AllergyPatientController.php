<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllergyPatientRequest;
use App\Http\Resources\AllergyPatientResource;
use App\Models\Allergy;
use App\Models\AllergyPatient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AllergyPatientController extends Controller
{

    /**
     * Muestra una lista paginada de todas las relaciones entre alergias y pacientes.
     *
     * @OA\Get(
     *     path="/api/allergy-patients",
     *     summary="Lista paginada de relaciones entre alergias y pacientes",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de relaciones paginada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true,
     *                 description="Indica si la operación fue exitosa"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/AllergyPatientResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", description="Enlace a la primera página"),
     *                 @OA\Property(property="last", type="string", description="Enlace a la última página"),
     *                 @OA\Property(property="prev", type="string", description="Enlace a la página anterior"),
     *                 @OA\Property(property="next", type="string", description="Enlace a la página siguiente")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", description="Número de la página actual"),
     *                 @OA\Property(property="from", type="integer", description="Número del primer elemento en la página actual"),
     *                 @OA\Property(property="last_page", type="integer", description="Número de la última página"),
     *                 @OA\Property(property="links", type="array", description="Enlaces a todas las páginas", @OA\Items(type="string")),
     *                 @OA\Property(property="path", type="string", description="URL de la página actual"),
     *                 @OA\Property(property="per_page", type="integer", description="Número de elementos por página"),
     *                 @OA\Property(property="to", type="integer", description="Número del último elemento en la página actual"),
     *                 @OA\Property(property="total", type="integer", description="Número total de elementos")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false,
     *                 description="Indica si la operación fue exitosa"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error interno del servidor",
     *                 description="Mensaje de error"
     *             )
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $allergies = AllergyPatient::paginate(5);
            $allergies->getCollection()->transform(function ($allergy) {
                return new AllergyPatientResource($allergy);
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
     * Crea una nueva relación entre una alergia y un paciente.
     *
     * @OA\Post(
     *     path="/api/allergy-patients",
     *     summary="Crea una nueva relación entre una alergia y un paciente",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación entre alergia y paciente a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_user_id", type="integer", description="ID del paciente"),
     *             @OA\Property(property="allergy_id", type="integer", description="ID de la alergia")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Relación entre alergia y paciente creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AllergyPatientResource")
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
     * @param AllergyPatientRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AllergyPatientRequest $request): JsonResponse
    {
        try {
            $allergy_patient = AllergyPatient::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new AllergyPatientResource($allergy_patient)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva relación entre la alergia y el paciente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    /**
     * Muestra una relación entre alergia y paciente específica.
     *
     * @OA\Get(
     *     path="/api/allergy-patients/{id}",
     *     summary="Muestra una relación entre alergia y paciente específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la relación entre alergia y paciente a mostrar",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre alergia y paciente encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AllergyPatientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre alergia y paciente no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="allergy_patient not found")
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
            $allergy_patient = AllergyPatient::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new AllergyPatientResource($allergy_patient)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'allergy_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una allergy_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una relación entre alergia y paciente existente.
     *
     * @OA\Put(
     *     path="/api/allergy-patients/{id}",
     *     summary="Actualiza una relación entre alergia y paciente existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la relación entre alergia y paciente a actualizar",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación entre alergia y paciente a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_user_id", type="integer"),
     *             @OA\Property(property="allergy_id", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre alergia y paciente actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AllergyPatientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre alergia y paciente no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="allergy_patient not found")
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
     * @param AllergyPatientRequest $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AllergyPatientRequest $request, string $id): JsonResponse
    {
        try {
            $allergy_patient = AllergyPatient::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $allergy_patient->fill($request->only([
                'patient_user_id', 'allergy_id'
            ]));

            $allergy_patient->save();

            return response()->json([
                'success' => true,
                'data' => new AllergyPatientResource($allergy_patient)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'allergy_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar un allergy_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una relación entre alergia y paciente.
     *
     * @OA\Delete(
     *     path="/api/allergy-patients/{id}",
     *     summary="Elimina una relación entre alergia y paciente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la relación entre alergia y paciente a eliminar",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre alergia y paciente eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre alergia y paciente no encontrada",
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
    public function destroy(string $id): JsonResponse
    {
        try {
            $allergy_patient = AllergyPatient::findOrFail($id);
            $allergy_patient->delete();

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
