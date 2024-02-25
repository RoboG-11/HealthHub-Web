<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicationPatientRequest;
use App\Http\Resources\MedicationPatientResource;
use App\Models\MedicationPatient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicationPatientController extends Controller
{

    /**
     * Obtiene una lista paginada de las relaciones entre alergias y pacientes.
     *
     * @OA\Get(
     *     path="/api/medication-patients",
     *     summary="Obtiene una lista paginada de las relaciones entre alergias y pacientes",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de relaciones obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MedicationPatientResource")),
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
     *                 @OA\Property(property="links", type="array", @OA\Items(type="string")),
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
            $medicationPatients = MedicationPatient::paginate(5);
            $medicationPatients->getCollection()->transform(function ($medicationPatient) {
                return [
                    'id' => $medicationPatient->id,
                    'patient_user_id' => $medicationPatient->patient_user_id,
                    'medication_id' => $medicationPatient->medication_id,
                ];
            });

            $pagination = [
                'success' => true,
                'data' => $medicationPatients->items(),
                'links' => [
                    'first' => $medicationPatients->url(1),
                    'last' => $medicationPatients->url($medicationPatients->lastPage()),
                    'prev' => $medicationPatients->previousPageUrl(),
                    'next' => $medicationPatients->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $medicationPatients->currentPage(),
                    'from' => $medicationPatients->firstItem(),
                    'last_page' => $medicationPatients->lastPage(),
                    'links' => $medicationPatients->getUrlRange(1, $medicationPatients->lastPage()),
                    'path' => $medicationPatients->url(1),
                    'per_page' => $medicationPatients->perPage(),
                    'to' => $medicationPatients->lastItem(),
                    'total' => $medicationPatients->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los registros de medication_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva relación entre la alergia y el paciente.
     *
     * @OA\Post(
     *     path="/api/medication-patients",
     *     summary="Crea una nueva relación entre la alergia y el paciente",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_user_id", type="integer", example="1"),
     *             @OA\Property(property="medication_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Relación creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicationPatientResource")
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
     * @param MedicationPatientRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(MedicationPatientRequest $request): JsonResponse
    {
        try {
            $medication_patient = MedicationPatient::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new MedicationPatientResource($medication_patient)
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
     * Obtiene una relación entre alergia y paciente por su ID.
     *
     * @OA\Get(
     *     path="/api/medication-patients/{id}",
     *     summary="Obtiene una relación entre alergia y paciente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre alergia y paciente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicationPatientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="medication_patient not found")
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
            $medication_patient = MedicationPatient::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new MedicationPatientResource($medication_patient)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'medication_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una medication_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una relación entre alergia y paciente por su ID.
     *
     * @OA\Put(
     *     path="/api/medication-patients/{id}",
     *     summary="Actualiza una relación entre alergia y paciente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre alergia y paciente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_user_id", type="integer", example="1"),
     *             @OA\Property(property="medication_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicationPatientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="medication_patient not found")
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
     * @param MedicationPatientRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(MedicationPatientRequest $request, string $id): JsonResponse
    {
        try {
            $medication_patient = MedicationPatient::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $medication_patient->fill($request->only([
                'patient_user_id', 'medication_id'
            ]));

            $medication_patient->save();

            return response()->json([
                'success' => true,
                'data' => new MedicationPatientResource($medication_patient)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'medication_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar un medication_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una relación entre alergia y paciente por su ID.
     *
     * @OA\Delete(
     *     path="/api/medication-patients/{id}",
     *     summary="Elimina una relación entre alergia y paciente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre alergia y paciente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="medication_patient not found")
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
            $medication_patient = MedicationPatient::findOrFail($id);
            $medication_patient->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'medication_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una medication_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
