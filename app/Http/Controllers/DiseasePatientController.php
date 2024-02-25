<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiseasePatientRequest;
use App\Http\Requests\DiseaseRequest;
use App\Http\Resources\DiseasePatientResource;
use App\Models\DiseasePatient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiseasePatientController extends Controller
{

    /**
     * Obtiene una lista paginada de las relaciones entre enfermedades y pacientes.
     *
     * @OA\Get(
     *     path="/api/disease-patients",
     *     summary="Obtiene una lista paginada de las relaciones entre enfermedades y pacientes",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de relaciones entre enfermedades y pacientes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DiseasePatientResource")),
     *             @OA\Property(property="links", type="object", @OA\Property(property="first", type="string"), @OA\Property(property="last", type="string"), @OA\Property(property="prev", type="string"), @OA\Property(property="next", type="string"))),
     *             @OA\Property(property="meta", type="object", @OA\Property(property="current_page", type="integer", example="1"), @OA\Property(property="from", type="integer", example="1"), @OA\Property(property="last_page", type="integer", example="5"), @OA\Property(property="links", type="array", @OA\Items(type="string")), @OA\Property(property="path", type="string", example="/api/disease-patients"), @OA\Property(property="per_page", type="integer", example="5"), @OA\Property(property="to", type="integer", example="5"), @OA\Property(property="total", type="integer", example="25"))
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
            $diseasePatient = DiseasePatient::paginate(5);
            $diseasePatient->getCollection()->transform(function ($diseasePatient) {
                return [
                    'id' => $diseasePatient->id,
                    'patient_user_id' => $diseasePatient->patient_user_id,
                    'disease_id' => $diseasePatient->disease_id,
                ];
            });

            $pagination = [
                'success' => true,
                'data' => $diseasePatient->items(),
                'links' => [
                    'first' => $diseasePatient->url(1),
                    'last' => $diseasePatient->url($diseasePatient->lastPage()),
                    'prev' => $diseasePatient->previousPageUrl(),
                    'next' => $diseasePatient->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $diseasePatient->currentPage(),
                    'from' => $diseasePatient->firstItem(),
                    'last_page' => $diseasePatient->lastPage(),
                    'links' => $diseasePatient->getUrlRange(1, $diseasePatient->lastPage()),
                    'path' => $diseasePatient->url(1),
                    'per_page' => $diseasePatient->perPage(),
                    'to' => $diseasePatient->lastItem(),
                    'total' => $diseasePatient->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las enfermedades de los pacientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva relación entre enfermedades y pacientes.
     *
     * @OA\Post(
     *     path="/api/disease-patients",
     *     summary="Crea una nueva relación entre enfermedades y pacientes",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_user_id", type="integer", example="1"),
     *             @OA\Property(property="disease_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Relación creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DiseasePatientResource")
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
     * @param DiseasePatientRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DiseasePatientRequest $request): JsonResponse
    {
        try {
            $diseasePatient = DiseasePatient::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new DiseasePatientResource($diseasePatient)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva relación entre la enfermedades y el paciente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.'
            ], 500);
        }
    }

    /**
     * Obtiene una relación entre enfermedad y paciente por su ID.
     *
     * @OA\Get(
     *     path="/api/disease-patients/{id}",
     *     summary="Obtiene una relación entre enfermedad y paciente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre enfermedad y paciente",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre enfermedad y paciente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DiseasePatientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre enfermedad y paciente no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="disease_patient not found")
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
            $diseasePatient = DiseasePatient::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new DiseasePatientResource($diseasePatient)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'disease_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una disease_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una relación entre enfermedad y paciente por su ID.
     *
     * @OA\Put(
     *     path="/api/disease-patients/{id}",
     *     summary="Actualiza una relación entre enfermedad y paciente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre enfermedad y paciente",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la relación entre enfermedad y paciente a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_user_id", type="integer", example="1"),
     *             @OA\Property(property="disease_id", type="integer", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre enfermedad y paciente actualizada correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DiseasePatientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre enfermedad y paciente no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="disease_patient not found")
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
     * @param DiseasePatientRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DiseasePatientRequest $request, string $id): JsonResponse
    {
        try {
            $diseasePatient = DiseasePatient::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $diseasePatient->fill($request->only([
                'patient_user_id', 'allergy_id'
            ]));

            $diseasePatient->save();

            return response()->json([
                'success' => true,
                'data' => new DiseasePatientResource($diseasePatient)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'disease_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar un disease_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una relación entre enfermedad y paciente por su ID.
     *
     * @OA\Delete(
     *     path="/api/disease-patients/{id}",
     *     summary="Elimina una relación entre enfermedad y paciente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la relación entre enfermedad y paciente",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación entre enfermedad y paciente eliminada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Relación entre enfermedad y paciente no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="disease_patient not found")
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
            $diseasePatient = DiseasePatient::findOrFail($id);
            $diseasePatient->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'disease_patient not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una disease_patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
