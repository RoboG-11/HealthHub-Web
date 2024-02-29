<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicineRequest;
use App\Http\Resources\MedicineResource;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $medicines = Medicine::paginate(5);
            $medicines->getCollection()->transform(function ($medicine) {
                return new MedicineResource($medicine);
            });

            $pagination = [
                'success' => true,
                'data' => $medicines->items(),
                'links' => [
                    'first' => $medicines->url(1),
                    'last' => $medicines->url($medicines->lastPage()),
                    'prev' => $medicines->previousPageUrl(),
                    'next' => $medicines->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $medicines->currentPage(),
                    'from' => $medicines->firstItem(),
                    'last_page' => $medicines->lastPage(),
                    'links' => $medicines->getUrlRange(1, $medicines->lastPage()),
                    'path' => $medicines->url(1),
                    'per_page' => $medicines->perPage(),
                    'to' => $medicines->lastItem(),
                    'total' => $medicines->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los medicamentos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicineRequest $request): JsonResponse
    {
        try {
            $medicine = Medicine::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => new MedicineResource($medicine)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva medicina: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $medicine = Medicine::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new MedicineResource($medicine)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving medicine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicineRequest $request, string $id): JsonResponse
    {
        try {
            $medicine = Medicine::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $medicine->fill($request->validated());
            $medicine->save();

            return response()->json([
                'success' => true,
                'data' => new MedicineResource($medicine)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating medicine: ' . $e->getMessage());
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
            $medicine = Medicine::findOrFail($id);
            $medicine->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting medicine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
