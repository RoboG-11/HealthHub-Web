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
     * Obtiene una lista paginada de medicamentos.
     *
     * @OA\Get(
     *     path="/api/medicines",
     *     summary="Obtiene una lista paginada de medicamentos",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de medicamentos obtenida correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/MedicineResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="/api/medicines?page=1"),
     *                 @OA\Property(property="last", type="string", example="/api/medicines?page=last_page"),
     *                 @OA\Property(property="prev", type="string", example="/api/medicines?page=prev_page"),
     *                 @OA\Property(property="next", type="string", example="/api/medicines?page=next_page")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example="current_page"),
     *                 @OA\Property(property="from", type="integer", example="from_item"),
     *                 @OA\Property(property="last_page", type="integer", example="last_page"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="path", type="string", example="/api/medicines"),
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
     * @OA\Post(
     *     path="/api/medicines",
     *     summary="Crea un nuevo medicamento",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del medicamento a crear",
     *         @OA\JsonContent(
     *             @OA\Property(property="summary_id", type="integer", example="1"),
     *             @OA\Property(property="medicine_name", type="string", example="Paracetamol"),
     *             @OA\Property(property="dosage", type="string", example="500 mg"),
     *             @OA\Property(property="frequency", type="string", example="Twice daily"),
     *             @OA\Property(property="duration", type="string", example="7 days"),
     *             @OA\Property(property="notes", type="string", example="Take with food.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Medicamento creado correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicineResource")
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
     * @param MedicineRequest $request
     * @return \Illuminate\Http\JsonResponse
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
     * @OA\Get(
     *     path="/api/medicines/{id}",
     *     summary="Obtiene un medicamento por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del medicamento",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medicamento encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicineResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medicamento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Medicine not found")
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
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
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
     * Actualiza un medicamento existente.
     *
     * @OA\Put(
     *     path="/api/medicines/{id}",
     *     summary="Actualiza un medicamento existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del medicamento a actualizar",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del medicamento a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="summary_id", type="integer", example="1"),
     *             @OA\Property(property="medicine_name", type="string", example="Ibuprofeno"),
     *             @OA\Property(property="dosage", type="string", example="1 tableta"),
     *             @OA\Property(property="frequency", type="string", example="Cada 8 horas"),
     *             @OA\Property(property="duration", type="string", example="5 días"),
     *             @OA\Property(property="notes", type="string", example="Tomar con comida")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medicamento actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/MedicineResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medicamento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Medicine not found")
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
     * @param MedicineRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
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
     * Elimina un medicamento existente por su ID.
     *
     * @OA\Delete(
     *     path="/api/medicines/{id}",
     *     summary="Elimina un medicamento existente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del medicamento",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Medicamento eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Medicamento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Medicine not found")
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
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
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
