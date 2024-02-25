<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\UserController;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    private $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $users = Patient::paginate(5);
            $users->getCollection()->transform(function ($user) {
                return new PatientResource($user);
            });

            $pagination = [
                'success' => true,
                'data' => $users->items(),
                'links' => [
                    'first' => $users->url(1),
                    'last' => $users->url($users->lastPage()),
                    'prev' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'from' => $users->firstItem(),
                    'last_page' => $users->lastPage(),
                    'links' => $users->getUrlRange(1, $users->lastPage()),
                    'path' => $users->url(1),
                    'per_page' => $users->perPage(),
                    'to' => $users->lastItem(),
                    'total' => $users->total(),
                ],
            ];

            return response()->json($pagination, 200);
        } catch (QueryException $e) {
            Log::error('Error en la consulta al obtener todos los pacientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientRequest $patientRequest, UserRequest $userRequest): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userData = $userRequest->only([
                'name', 'last_name', 'email', 'password', 'phone', 'sex', 'age', 'date_of_birth', 'link_photo'
            ]);
            $userData['rol'] = 'paciente';

            $userController = new UserController();
            $userResponse = $userController->store(new UserRequest($userData));

            if ($userResponse->getStatusCode() !== 201 || !$userResponse->getData()->success) {
                DB::rollBack();
                return $userResponse;
            }

            $user = User::find($userResponse->getData()->data->id);

            $patientData = $patientRequest->validated();
            $patientData['user_id'] = $userResponse->getData()->data->id;
            $patientData['weight'] = $patientRequest->input('weight');
            $patientData['height'] = $patientRequest->input('height');
            $patientData['nss'] = $patientRequest->input('nss');
            $patientData['occupation'] = $patientRequest->input('occupation');
            $patientData['blood_type'] = $patientRequest->input('blood_type');
            $patientData['emergency_contact_phone'] = $patientRequest->input('emergency_contact_phone');

            $patient = Patient::create($patientData);

            DB::commit();

            $patientJson = new PatientResource($patient);
            $patientJson = $patientJson->toArray($patientRequest);
            $patientJson['personal_information'] = $userResponse->getData()->data;

            $token = $user->createToken('API TOKEN')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => $patientJson,
                'token' => $token
            ], 201);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Error al crear un nuevo paciente: ' . $e->getMessage());
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
            $patient = Patient::with('user')->where('user_id', $id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => new PatientResource($patient)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener un paciente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $patientRequest, UserRequest $userRequest, string $id)
    {
        try {
            $patient = Patient::where('user_id', $id)->firstOrFail();
            $user = User::findOrFail($patient->user_id);

            $patient->weight = $patientRequest->input('weight');
            $patient->height = $patientRequest->input('height');
            $patient->nss = $patientRequest->input('nss');
            $patient->occupation = $patientRequest->input('occupation');
            $patient->blood_type = $patientRequest->input('blood_type');
            $patient->emergency_contact_phone = $patientRequest->input('emergency_contact_phone');
            $patient->save();

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
                    'patient' => new PatientResource($patient),
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente o usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al actualizar paciente y usuario: ' . $e->getMessage());
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
            $patient = Patient::where('user_id', $id)->firstOrFail();

            $user = User::findOrFail($id);
            $patient->delete();
            $user->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar un paciente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::id());
            $token = $user->createToken('API TOKEN')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Credenciales inválidas'
        ], 401);
    }
}
