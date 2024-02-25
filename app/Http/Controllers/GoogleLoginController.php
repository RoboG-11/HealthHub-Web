<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * @OA\Info(
 *     title="API de autenticación con Google",
 *     version="1.0.0",
 *     description="Esta API permite autenticarse con Google y registrar nuevos usuarios."
 * )
 * @OA\Server(url="http://127.0.0.1:8000")
 */
class GoogleLoginController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/google/redirect",
     *     summary="Redirige al usuario a la página de autenticación de Google",
     *     @OA\Response(
     *         response=302,
     *         description="Redirige al usuario a la página de autenticación de Google",
     *         @OA\MediaType(
     *             mediaType="text/html",
     *             @OA\Schema(
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();

            $userExists = User::where('external_id', $user->id)->where('external_auth', 'google')->first();

            if ($userExists) {
                Auth::login($userExists);

                $token = $userExists->createToken('API TOKEN')->plainTextToken;

                return redirect('/#' . $token, 302);
            } else {
                $userData = [
                    'name' => $user->user['given_name'],
                    'last_name' => $user->user['family_name'],
                    'email' => $user->email,
                    'external_id' => $user->id,
                    'external_auth' => 'google'
                ];

                $userNew = User::create($userData);
                $token = $userNew->createToken('API TOKEN')->plainTextToken;

                Auth::login($userNew);

                return redirect('/#' . $token, 302);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del usuario de Google: ' . $e->getMessage()
            ], 500);
        }
    }
}
