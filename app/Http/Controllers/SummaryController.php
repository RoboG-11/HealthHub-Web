<?php

namespace App\Http\Controllers;

use App\Http\Requests\SummaryRequest;
use App\Http\Resources\SummaryResource;
use App\Models\Summary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $summaries = Summary::paginate(5);
            $summaries->getCollection()->transform(function ($summary) {
                return new SummaryResource($summary);
            });

            $pagination = [
                'success' => true,
                'data' => $summaries->items(),
                'links' => [
                    'first' => $summaries->url(1),
                    'last' => $summaries->url($summaries->lastPage()),
                    'prev' => $summaries->previousPageUrl(),
                    'next' => $summaries->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $summaries->currentPage(),
                    'from' => $summaries->firstItem(),
                    'last_page' => $summaries->lastPage(),
                    'links' => $summaries->getUrlRange(1, $summaries->lastPage()),
                    'path' => $summaries->url(1),
                    'per_page' => $summaries->perPage(),
                    'to' => $summaries->lastItem(),
                    'total' => $summaries->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los resúmenes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SummaryRequest $request): JsonResponse
    {
        try {
            $summary = Summary::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => new SummaryResource($summary)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear un nuevo resumen: ' . $e->getMessage());
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
            $summary = Summary::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new SummaryResource($summary)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Summary not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SummaryRequest $request, string $id): JsonResponse
    {
        try {
            $summary = Summary::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $summary->fill($request->validated());
            $summary->save();

            return response()->json([
                'success' => true,
                'data' => new SummaryResource($summary)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Summary not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating summary: ' . $e->getMessage());
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
            $summary = Summary::findOrFail($id);
            $summary->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Summary not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
