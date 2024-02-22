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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
