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
