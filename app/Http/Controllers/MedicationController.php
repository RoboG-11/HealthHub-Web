<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicationRequest;
use App\Http\Resources\MedicationResource;
use App\Models\Medication;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $medications = Medication::paginate(5);
            $medications->getCollection()->transform(function ($medication) {
                return [
                    'id' => $medication->id,
                    'medication_name' => $medication->medication_name,
                    'description' => $medication->description,
                ];
            });

            $pagination = [
                'success' => true,
                'data' => $medications->items(),
                'links' => [
                    'first' => $medications->url(1),
                    'last' => $medications->url($medications->lastPage()),
                    'prev' => $medications->previousPageUrl(),
                    'next' => $medications->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $medications->currentPage(),
                    'from' => $medications->firstItem(),
                    'last_page' => $medications->lastPage(),
                    'links' => $medications->getUrlRange(1, $medications->lastPage()),
                    'path' => $medications->url(1),
                    'per_page' => $medications->perPage(),
                    'to' => $medications->lastItem(),
                    'total' => $medications->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las medicaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicationRequest $request): JsonResponse
    {
        try {
            $medications = Medication::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new MedicationResource($medications)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva medicación: ' . $e->getMessage());
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
            $medications = Medication::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new MedicationResource($medications)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Medication not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una Medicación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicationRequest $request, string $id): JsonResponse
    {
        try {
            $medications = Medication::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $medications->fill($request->only([
                'medication_name', 'description',
            ]));

            $medications->save();

            return response()->json([
                'success' => true,
                'data' => new MedicationResource($medications)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'medicationn not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una medicación: ' . $e->getMessage());
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
            $medications = Medication::findOrFail($id);
            $medications->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'medication not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una medicación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
