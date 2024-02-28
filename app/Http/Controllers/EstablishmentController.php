<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstablishmentRequest;
use App\Http\Resources\EstablishmentResource;
use App\Models\Establishment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstablishmentController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $establishments = Establishment::paginate(5);
            $establishments->getCollection()->transform(function ($establishment) {
                return new EstablishmentResource($establishment);
            });

            $pagination = [
                'success' => true,
                'data' => $establishments->items(),
                'links' => [
                    'first' => $establishments->url(1),
                    'last' => $establishments->url($establishments->lastPage()),
                    'prev' => $establishments->previousPageUrl(),
                    'next' => $establishments->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $establishments->currentPage(),
                    'from' => $establishments->firstItem(),
                    'last_page' => $establishments->lastPage(),
                    'links' => $establishments->getUrlRange(1, $establishments->lastPage()),
                    'path' => $establishments->url(1),
                    'per_page' => $establishments->perPage(),
                    'to' => $establishments->lastItem(),
                    'total' => $establishments->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los establecimientos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function store(EstablishmentRequest $request): JsonResponse
    {
        try {
            $establishment = Establishment::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $establishment
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear un nuevo establecimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $establishment = Establishment::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new EstablishmentResource($establishment)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Establishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener un establishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function update(EstablishmentRequest $request, string $id): JsonResponse
    {
        try {
            $establishment = Establishment::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $establishment->fill($request->only([
                'establishment_name', 'establishment_type', 'website_url',
            ]));

            $establishment->save();

            return response()->json([
                'success' => true,
                'data' => new EstablishmentResource($establishment)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Establishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar un establishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $establishment = Establishment::findOrFail($id);
            $establishment->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Establishment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting establishment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
