<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    /**
     * Obtiene una lista paginada de citas.
     *
     * @OA\Get(
     *     path="/api/appointments",
     *     summary="Obtiene una lista paginada de citas",
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de citas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AppointmentResource")),
     *             @OA\Property(property="links", type="object", @OA\Property(property="first", type="string"), @OA\Property(property="last", type="string"), @OA\Property(property="prev", type="string"), @OA\Property(property="next", type="string")),
     *             @OA\Property(property="meta", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="from", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="links", type="array", @OA\Items(type="string")), @OA\Property(property="path", type="string"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="to", type="integer"), @OA\Property(property="total", type="integer"))
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
            $appointments = Appointment::paginate(5);
            $appointments->getCollection()->transform(function ($appointment) {
                return new AppointmentResource($appointment);
            });

            $pagination = [
                'success' => true,
                'data' => $appointments->items(),
                'links' => [
                    'first' => $appointments->url(1),
                    'last' => $appointments->url($appointments->lastPage()),
                    'prev' => $appointments->previousPageUrl(),
                    'next' => $appointments->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $appointments->currentPage(),
                    'from' => $appointments->firstItem(),
                    'last_page' => $appointments->lastPage(),
                    'links' => $appointments->getUrlRange(1, $appointments->lastPage()),
                    'path' => $appointments->url(1),
                    'per_page' => $appointments->perPage(),
                    'to' => $appointments->lastItem(),
                    'total' => $appointments->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las citas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva cita.
     *
     * @OA\Post(
     *     path="/api/appointments",
     *     summary="Crea una nueva cita",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la cita a crear",
     *         @OA\JsonContent(
     *             required={"user_id", "patient_id", "date", "notes"},
     *             @OA\Property(property="user_id", type="integer", example="1"),
     *             @OA\Property(property="patient_id", type="integer", example="1"),
     *             @OA\Property(property="date", type="string", format="date-time", example="2024-03-02 10:00:00"),
     *             @OA\Property(property="notes", type="string", example="Checkup"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cita creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AppointmentResource"),
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
     * @param  \App\Http\Requests\AppointmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = Appointment::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $appointment
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva cita: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    /**
     * Obtiene una cita por su ID.
     *
     * @OA\Get(
     *     path="/api/appointments/{id}",
     *     summary="Obtiene una cita por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la cita",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cita encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AppointmentResource"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cita no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Appointment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new AppointmentResource($appointment)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una cita: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una cita por su ID.
     *
     * @OA\Put(
     *     path="/api/appointments/{id}",
     *     summary="Actualiza una cita por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la cita",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la cita a actualizar",
     *         @OA\JsonContent(
     *             required={"user_id", "patient_id", "date", "notes"},
     *             @OA\Property(property="user_id", type="integer", example="1"),
     *             @OA\Property(property="patient_id", type="integer", example="1"),
     *             @OA\Property(property="date", type="string", format="date-time", example="2024-03-02 10:00:00"),
     *             @OA\Property(property="notes", type="string", example="Checkup"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cita actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AppointmentResource"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cita no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Appointment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     *
     * @param  \App\Http\Requests\AppointmentRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AppointmentRequest $request, string $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $appointment->fill($request->validated());
            $appointment->save();

            return response()->json([
                'success' => true,
                'data' => new AppointmentResource($appointment)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una cita: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una cita por su ID.
     *
     * @OA\Delete(
     *     path="/api/appointments/{id}",
     *     summary="Elimina una cita por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la cita",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cita eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cita no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Appointment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
