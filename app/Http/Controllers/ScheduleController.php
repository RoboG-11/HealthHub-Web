<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $schedules = Schedule::paginate(5);
            $schedules->getCollection()->transform(function ($schedule) {
                return new ScheduleResource($schedule);
            });

            $pagination = [
                'success' => true,
                'data' => $schedules->items(),
                'links' => [
                    'first' => $schedules->url(1),
                    'last' => $schedules->url($schedules->lastPage()),
                    'prev' => $schedules->previousPageUrl(),
                    'next' => $schedules->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $schedules->currentPage(),
                    'from' => $schedules->firstItem(),
                    'last_page' => $schedules->lastPage(),
                    'links' => $schedules->getUrlRange(1, $schedules->lastPage()),
                    'path' => $schedules->url(1),
                    'per_page' => $schedules->perPage(),
                    'to' => $schedules->lastItem(),
                    'total' => $schedules->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los horarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = Schedule::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => new ScheduleResource($schedule)
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear un nuevo horario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $doctor_id): JsonResponse
    {
        try {
            $schedules = Schedule::where('doctor_id', $doctor_id)->get();

            return response()->json([
                'success' => true,
                'data' => ScheduleResource::collection($schedules)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener los horarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScheduleRequest $request, string $id): JsonResponse
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $schedule->fill($request->validated());
            $schedule->save();

            return response()->json([
                'success' => true,
                'data' => new ScheduleResource($schedule)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating schedule: ' . $e->getMessage());
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
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
