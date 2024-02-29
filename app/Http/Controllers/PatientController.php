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
     * Obtiene una lista paginada de pacientes.
     *
     * @OA\Get(
     *     path="/api/patients",
     *     summary="Obtiene una lista paginada de pacientes",
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de pacientes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/PatientResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
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
     * Crea un nuevo paciente.
     *
     * @OA\Post(
     *     path="/api/patients",
     *     summary="Crea un nuevo paciente",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del paciente a crear",
     *         @OA\JsonContent(
     *             required={
     *                 "name",
     *                 "last_name",
     *                 "email",
     *                 "password",
     *                 "phone",
     *                 "sex",
     *                 "age",
     *                 "date_of_birth",
     *                 "link_photo",
     *                 "weight",
     *                 "height",
     *                 "nss",
     *                 "occupation",
     *                 "blood_type",
     *                 "emergency_contact_phone"
     *             },
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="sex", type="string", enum={"M", "F"}),
     *             @OA\Property(property="age", type="integer"),
     *             @OA\Property(property="date_of_birth", type="string", format="date"),
     *             @OA\Property(property="link_photo", type="string"),
     *             @OA\Property(property="weight", type="number", format="float"),
     *             @OA\Property(property="height", type="number", format="float"),
     *             @OA\Property(property="nss", type="string"),
     *             @OA\Property(property="occupation", type="string"),
     *             @OA\Property(property="blood_type", type="string"),
     *             @OA\Property(property="emergency_contact_phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paciente creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/PatientResource"
     *             ),
     *             @OA\Property(property="token", type="string")
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
     * @param PatientRequest $patientRequest
     * @param UserRequest $userRequest
     * @return \Illuminate\Http\JsonResponse
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
     * Muestra los detalles de un paciente por su ID de usuario.
     *
     * @OA\Get(
     *     path="/api/patients/{id}",
     *     summary="Muestra los detalles de un paciente por su ID de usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de usuario del paciente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del paciente mostrados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/PatientResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paciente no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Paciente no encontrado")
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
     * Actualiza los datos de un paciente y su usuario asociado.
     *
     * @OA\Put(
     *     path="/api/patients/{id}",
     *     summary="Actualizar paciente y usuario",
     *     operationId="updatePatient",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del paciente a actualizar",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del paciente y usuario a actualizar",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="weight", type="string", example="70"),
     *             @OA\Property(property="height", type="string", example="180"),
     *             @OA\Property(property="nss", type="string", example="123456789"),
     *             @OA\Property(property="occupation", type="string", example="Engineer"),
     *             @OA\Property(property="blood_type", type="string", example="AB+"),
     *             @OA\Property(property="emergency_contact_phone", type="string", example="555-555-5555"),
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="phone", type="string", example="555-555-5555"),
     *             @OA\Property(property="sex", type="string", example="Male"),
     *             @OA\Property(property="age", type="integer", example=30),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="link_photo", type="string", example="http://example.com/photo.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(
     *                     property="patient",
     *                     ref="#/components/schemas/PatientResource"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paciente o usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paciente o usuario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
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
     * Elimina un paciente y su usuario asociado por el ID del usuario.
     *
     * @OA\Delete(
     *     path="/api/patients/{id}",
     *     summary="Elimina un paciente y su usuario asociado por el ID del usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de usuario del paciente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paciente y usuario eliminados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paciente no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Paciente no encontrado")
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
}
