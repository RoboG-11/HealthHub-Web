<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $doctors = Doctor::paginate(5);
            $doctors->getCollection()->transform(function ($doctor) {
                return new DoctorResource($doctor);
            });

            $pagination = [
                'success' => true,
                'data' => $doctors->items(),
                'links' => [
                    'first' => $doctors->url(1),
                    'last' => $doctors->url($doctors->lastPage()),
                    'prev' => $doctors->previousPageUrl(),
                    'next' => $doctors->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $doctors->currentPage(),
                    'from' => $doctors->firstItem(),
                    'last_page' => $doctors->lastPage(),
                    'links' => $doctors->getUrlRange(1, $doctors->lastPage()),
                    'path' => $doctors->url(1),
                    'per_page' => $doctors->perPage(),
                    'to' => $doctors->lastItem(),
                    'total' => $doctors->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los doctores: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorRequest $doctorRequest, UserRequest $userRequest): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userData = $userRequest->only([
                'name', 'last_name', 'email', 'password', 'phone', 'sex', 'age', 'date_of_birth', 'link_photo'
            ]);
            $userData['rol'] = 'doctor';

            $userController = new UserController();
            $userResponse = $userController->store(new UserRequest($userData));

            if ($userResponse->getStatusCode() !== 201 || !$userResponse->getData()->success) {
                DB::rollBack();
                return $userResponse;
            }

            $user = User::find($userResponse->getData()->data->id);

            $doctorData = $doctorRequest->validated();
            $doctorData['user_id'] = $userResponse->getData()->data->id;
            $doctorData['professional_license'] = $doctorRequest->input('professional_license');
            $doctorData['education'] = $doctorRequest->input('education');
            $doctorData['consultation_cost'] = $doctorRequest->input('consultation_cost');

            $doctor = Doctor::create($doctorData);

            DB::commit();

            $doctorJson = new DoctorResource($doctor);
            $doctorJson = $doctorJson->toArray($doctorRequest);
            $doctorJson['personal_information'] = $userResponse->getData()->data;

            $token = $user->createToken('API TOKEN')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => $doctorJson,
                'token' => $token
            ], 201);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Error al crear un nuevo doctor: ' . $e->getMessage());
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
            $doctor = Doctor::with('user')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new DoctorResource($doctor)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener un doctor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * 
     * Update the specified resource in storage.
     */
    public function update(DoctorRequest $doctorRequest, UserRequest $userRequest, string $id)
    {
        try {
            $doctor = Doctor::where('user_id', $id)->firstOrFail();
            $user = User::findOrFail($doctor->user_id);

            $doctor->professional_license = $doctorRequest->input('professional_license');
            $doctor->education = $doctorRequest->input('education');
            $doctor->consultation_cost = $doctorRequest->input('consultation_cost');
            $doctor->save();

            $user->name = $userRequest->input('name');
            $user->last_name = $userRequest->input('last_name');
            $user->email = $userRequest->input('email');
            $user->phone = $userRequest->input('phone');
            $user->sex = $userRequest->input('sex');
            $user->age = $userRequest->input('age');
            $user->date_of_birth = $userRequest->input('date_of_birth');
            $user->link_photo = $userRequest->input('link_photo');
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'doctor' => new DoctorResource($doctor),
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor o usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar doctor y usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $doctor = Doctor::where('user_id', $id)->firstOrFail();

            $user = User::findOrFail($id);
            $doctor->delete();
            $user->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar un doctor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    // BUG: Se debe de agregar el LOGIN igual qu al paciente????
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     if (Auth::attempt($credentials)) {
    //         $user = User::find(Auth::id());
    //         $token = $user->createToken('API TOKEN')->plainTextToken;

    //         return response()->json([
    //             'success' => true,
    //             'token' => $token,
    //             'user' => $user
    //         ], 200);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Credenciales inválidas'
    //     ], 401);
    // }
}
