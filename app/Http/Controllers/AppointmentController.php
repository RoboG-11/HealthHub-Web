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
     * Display a listing of the resource.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
