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
     * Obtiene una lista paginada de horarios.
     *
     * @OA\Get(
     *     path="/api/schedules",
     *     summary="Obtiene una lista paginada de horarios",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de horarios obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ScheduleResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="/api/schedules?page=1"),
     *                 @OA\Property(property="last", type="string", example="/api/schedules?page=last_page"),
     *                 @OA\Property(property="prev", type="string", example="/api/schedules?page=prev_page"),
     *                 @OA\Property(property="next", type="string", example="/api/schedules?page=next_page")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example="current_page"),
     *                 @OA\Property(property="from", type="integer", example="from_item"),
     *                 @OA\Property(property="last_page", type="integer", example="last_page"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="path", type="string", example="/api/schedules"),
     *                 @OA\Property(property="per_page", type="integer", example="per_page"),
     *                 @OA\Property(property="to", type="integer", example="to_item"),
     *                 @OA\Property(property="total", type="integer", example="total_items")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Crea un nuevo horario.
     *
     * @OA\Post(
     *     path="/api/schedules",
     *     summary="Crea un nuevo horario",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del horario a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="doctor_id", type="integer", example="1"),
     *             @OA\Property(property="start_time", type="string", format="time", example="08:00:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="09:00:00"),
     *             @OA\Property(property="day_of_week", type="string", example="Monday")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Horario creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/ScheduleResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor.")
     *         )
     *     )
     * )
     *
     * @param ScheduleRequest $request
     * @return \Illuminate\Http\JsonResponse
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
     * Muestra los horarios de un doctor específico.
     *
     * @OA\Get(
     *     path="/api/schedules/{doctor_id}",
     *     summary="Muestra los horarios de un doctor específico",
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="path",
     *         required=true,
     *         description="ID del doctor",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horarios obtenidos correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ScheduleResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     *
     * @param string $doctor_id
     * @return \Illuminate\Http\JsonResponse
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
     * Actualiza un horario existente.
     *
     * @OA\Put(
     *     path="/api/schedules/{id}",
     *     summary="Actualiza un horario existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del horario a actualizar",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del horario a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="doctor_id", type="integer", example="1"),
     *             @OA\Property(property="patient_id", type="integer", example="2"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-03-01"),
     *             @OA\Property(property="time", type="string", format="time", example="08:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horario actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/ScheduleResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Horario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Horario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     *
     * @param ScheduleRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
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
     * Elimina un horario existente.
     *
     * @OA\Delete(
     *     path="/api/schedules/{id}",
     *     summary="Elimina un horario existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del horario a eliminar",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Horario eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Horario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Horario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
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
