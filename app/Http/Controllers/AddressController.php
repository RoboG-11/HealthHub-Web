<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    /**
     * Obtiene una lista paginada de direcciones.
     *
     * @OA\Get(
     *     path="/api/addresses",
     *     summary="Obtiene una lista paginada de direcciones",
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de direcciones",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AddressResource")),
     *             @OA\Property(property="links", type="object", @OA\Property(property="first", type="string"), @OA\Property(property="last", type="string"), @OA\Property(property="prev", type="string"), @OA\Property(property="next", type="string")),
     *             @OA\Property(property="meta", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="from", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="links", type="array", @OA\Items(type="string")), @OA\Property(property="path", type="string"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="to", type="integer"), @OA\Property(property="total", type="integer"))
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $addresses = Address::paginate(5);
            $addresses->getCollection()->transform(function ($address) {
                return new AddressResource($address);
            });

            $pagination = [
                'success' => true,
                'data' => $addresses->items(),
                'links' => [
                    'first' => $addresses->url(1),
                    'last' => $addresses->url($addresses->lastPage()),
                    'prev' => $addresses->previousPageUrl(),
                    'next' => $addresses->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $addresses->currentPage(),
                    'from' => $addresses->firstItem(),
                    'last_page' => $addresses->lastPage(),
                    'links' => $addresses->getUrlRange(1, $addresses->lastPage()),
                    'path' => $addresses->url(1),
                    'per_page' => $addresses->perPage(),
                    'to' => $addresses->lastItem(),
                    'total' => $addresses->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todas las direcciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Crea una nueva dirección.
     *
     * @OA\Post(
     *     path="/api/addresses",
     *     summary="Crea una nueva dirección",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la dirección a crear",
     *         @OA\JsonContent(
     *             required={"street", "city", "state", "zip_code", "country"},
     *             @OA\Property(property="street", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="Springfield"),
     *             @OA\Property(property="state", type="string", example="IL"),
     *             @OA\Property(property="zip_code", type="string", example="12345"),
     *             @OA\Property(property="country", type="string", example="USA"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dirección creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AddressResource"),
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
     * @param  \App\Http\Requests\AddressRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AddressRequest $request): JsonResponse
    {
        try {
            $address = Address::create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $address
            ], 201);
        } catch (QueryException $e) {
            Log::error('Error al crear una nueva dirección: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.'
            ], 500);
        }
    }

    /**
     * Obtiene una dirección por su ID.
     *
     * @OA\Get(
     *     path="/api/addresses/{id}",
     *     summary="Obtiene una dirección por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la dirección",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dirección encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AddressResource"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dirección no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Address not found")
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
            $address = Address::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new AddressResource($address)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener una dirección: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Actualiza una dirección existente por su ID.
     *
     * @OA\Put(
     *     path="/api/addresses/{id}",
     *     summary="Actualiza una dirección existente por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la dirección",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la dirección a actualizar",
     *         @OA\JsonContent(
     *             required={"street", "city", "state", "zip_code", "country"},
     *             @OA\Property(property="street", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="Springfield"),
     *             @OA\Property(property="state", type="string", example="IL"),
     *             @OA\Property(property="zip_code", type="string", example="12345"),
     *             @OA\Property(property="country", type="string", example="USA"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dirección actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/AddressResource"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dirección no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Address not found")
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
     * @param  \App\Http\Requests\AddressRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AddressRequest $request, string $id): JsonResponse
    {
        try {
            $address = Address::findOrFail($id);

            // Actualizar solo los campos que se envían en la solicitud
            $address->fill($request->validated());
            $address->save();

            return response()->json([
                'success' => true,
                'data' => new AddressResource($address)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar una dirección: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Elimina una dirección por su ID.
     *
     * @OA\Delete(
     *     path="/api/addresses/{id}",
     *     summary="Elimina una dirección por su ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la dirección",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dirección eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dirección no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Address not found")
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
            $address = Address::findOrFail($id);
            $address->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting address: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
