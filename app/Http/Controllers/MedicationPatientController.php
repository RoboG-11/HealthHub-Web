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
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
