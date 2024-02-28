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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
