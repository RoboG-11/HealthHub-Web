<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRequest;
use App\Http\Requests\DoctorUpdateRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\DoctorPublicResource;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    /**
     * Obtiene una lista paginada de doctores.
     *
     * @OA\Get(
     *     path="/api/doctors",
     *     summary="Obtiene una lista paginada de doctores",
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de doctores",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DoctorResource")),
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
     * Crea un nuevo doctor.
     *
     * @OA\Post(
     *     path="/api/doctors",
     *     summary="Crea un nuevo doctor",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del doctor a crear",
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
     *                 "professional_license",
     *                 "education",
     *                 "consultation_cost"
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
     *             @OA\Property(property="professional_license", type="string"),
     *             @OA\Property(property="education", type="string"),
     *             @OA\Property(property="consultation_cost", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Doctor creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/DoctorResource"
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
     * @param DoctorRequest $doctorRequest
     * @param UserRequest $userRequest
     * @return \Illuminate\Http\JsonResponse
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

            $daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

            foreach ($daysOfWeek as $day) {
                $schedule = new Schedule();
                $schedule->start_time = null;
                $schedule->end_time = null;
                $schedule->day_of_week = $day;
                $schedule->doctor_id = $user->id;
                $schedule->save();
            }

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
     * Muestra los detalles de un doctor.
     *
     * @OA\Get(
     *     path="/api/doctors/{id}",
     *     summary="Muestra los detalles de un doctor",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del doctor a mostrar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/DoctorResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Doctor no encontrado")
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
     * Actualiza los datos de un doctor y su usuario asociado.
     *
     * @OA\Put(
     *     path="/api/doctors/{id}",
     *     summary="Actualiza los datos de un doctor y su usuario asociado",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del doctor a actualizar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del doctor a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="professional_license", type="string"),
     *             @OA\Property(property="education", type="string"),
     *             @OA\Property(property="consultation_cost", type="number", format="float"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="sex", type="string", enum={"M", "F"}),
     *             @OA\Property(property="age", type="integer"),
     *             @OA\Property(property="date_of_birth", type="string", format="date"),
     *             @OA\Property(property="link_photo", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Doctor y usuario actualizados exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DoctorResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor o usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Doctor o usuario no encontrado")
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
     * @param DoctorRequest $doctorRequest
     * @param UserRequest $userRequest
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(DoctorUpdateRequest $doctorRequest, UserUpdateRequest $userRequest)
    {
        try {
            $doctor = Auth::user()->doctor;
            $user = $doctor->user;

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
     * Elimina al doctor autenticado y su usuario asociado.
     *
     * @OA\Delete(
     *     path="/api/doctors",
     *     summary="Elimina al doctor autenticado y su usuario asociado",
     *     @OA\Response(
     *         response=200,
     *         description="Doctor y usuario eliminados exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Doctor no encontrado")
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
    public function destroy(): JsonResponse
    {
        try {
            $doctor = Auth::user()->doctor;

            $user = $doctor->user;
            $doctor->delete();
            $user->delete();

            return response()->json([
                'success' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting doctor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }


    /**
     * Obtiene la información del doctor autenticado.
     *
     * @OA\Get(
     *     path="/api/doctors/info",
     *     summary="Obtiene la información del doctor autenticado",
     *     @OA\Response(
     *         response=200,
     *         description="Información del doctor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="data", ref="#/components/schemas/DoctorResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Doctor no encontrado")
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
    public function info(): JsonResponse
    {
        try {
            $doctor = Auth::user()->doctor;

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new DoctorResource($doctor)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener la información del doctor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }


    public function doctors_publics(): JsonResponse
    {
        try {
            $doctors = Doctor::paginate(20);
            $doctors->getCollection()->transform(function ($doctor) {
                return new DoctorPublicResource($doctor);
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
}
