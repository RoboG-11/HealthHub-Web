<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllergyRequest;
use App\Http\Resources\AllergyResource;
use App\Models\Allergy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AllergyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $allergies = Allergy::paginate(5);
            $allergies->getCollection()->transform(function ($allergy) {
                return new AllergyResource($allergy);
            });

            $pagination = [
                'success' => true,
                'data' => $allergies->items(),
                'links' => [
                    'first' => $allergies->url(1),
                    'last' => $allergies->url($allergies->lastPage()),
                    'prev' => $allergies->previousPageUrl(),
                    'next' => $allergies->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $allergies->currentPage(),
                    'from' => $allergies->firstItem(),
                    'last_page' => $allergies->lastPage(),
                    'links' => $allergies->getUrlRange(1, $allergies->lastPage()),
                    'path' => $allergies->url(1),
                    'per_page' => $allergies->perPage(),
                    'to' => $allergies->lastItem(),
                    'total' => $allergies->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las alergias: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllergyRequest $request): JsonResponse
    {
        try {
            $allergy = Allergy::create($request->all());

            return response()->json([
                'success' => true,
                'data' => new AllergyResource($allergy)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva alergia: ' . $e->getMessage());
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
            $allergy = Allergy::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new AllergyResource($allergy)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allergy not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una alergia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AllergyRequest $request, string $id): JsonResponse
    {
        try {
            $allergy = Allergy::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $allergy->fill($request->only([
                'allergy_name', 'description',
            ]));

            $allergy->save();

            return response()->json([
                'success' => true,
                'data' => new AllergyResource($allergy)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allergy not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una alergia: ' . $e->getMessage());
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
            $allergy = Allergy::findOrFail($id);
            $allergy->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Allergy not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar una alergia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
