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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
