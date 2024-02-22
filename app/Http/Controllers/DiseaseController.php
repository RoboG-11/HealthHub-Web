<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiseaseRequest;
use App\Http\Resources\DiseaseResource;
use App\Models\Disease;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $diseases = Disease::paginate(5);
            $diseases->getCollection()->transform(function ($disease) {
                return [
                    'id' => $disease->id,
                    'disease_name' => $disease->disease_name,
                    'description' => $disease->description,
                ];
            });

            $pagination = [
                'success' => true,
                'data' => $diseases->items(),
                'links' => [
                    'first' => $diseases->url(1),
                    'last' => $diseases->url($diseases->lastPage()),
                    'prev' => $diseases->previousPageUrl(),
                    'next' => $diseases->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $diseases->currentPage(),
                    'from' => $diseases->firstItem(),
                    'last_page' => $diseases->lastPage(),
                    'links' => $diseases->getUrlRange(1, $diseases->lastPage()),
                    'path' => $diseases->url(1),
                    'per_page' => $diseases->perPage(),
                    'to' => $diseases->lastItem(),
                    'total' => $diseases->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las enfermedades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DiseaseRequest $request): JsonResponse
    {
        try {
            $diseases = Disease::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new DiseaseResource($diseases)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva enfermedad: ' . $e->getMessage());
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
            $disease = Disease::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new DiseaseResource($disease)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una enfermedad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DiseaseRequest $request, string $id): JsonResponse
    {
        try {
            $disease = Disease::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $disease->fill($request->only([
                'disease_name', 'description',
            ]));

            $disease->save();

            return response()->json([
                'success' => true,
                'data' => new DiseaseResource($disease)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una enfermedad: ' . $e->getMessage());
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
            $disease = Disease::findOrFail($id);
            $disease->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una enfermedad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
