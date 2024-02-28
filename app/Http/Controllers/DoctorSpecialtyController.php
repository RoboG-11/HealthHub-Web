<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorSpecialtyRequest;
use App\Http\Resources\DoctorSpecialtyResource;
use App\Models\DoctorSpecialty;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorSpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $doctorSpecialties = DoctorSpecialty::paginate(5);
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

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
