<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        $token = $user->createToken(
            $request->device_name ?? 'mobile-' . $request->userAgent(),
            ['mobile']
        )->plainTextToken;

        $tenants = $user->activeTenants->map(function ($tenant) {
            return [
                'id' => $tenant->id,
                'uuid' => $tenant->uuid,
                'company_name' => $tenant->company_name,
                'role' => $tenant->pivot->role,
                'status' => $tenant->status,
            ];
        });

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'language' => $user->language,
                ],
                'tenants' => $tenants,
            ],
            'message' => 'Login exitoso',
            'status' => 200,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => null,
            'message' => 'Sesión cerrada',
            'status' => 200,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('activeTenants');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'language' => $user->language,
                'is_superadmin' => $user->is_superadmin,
                'tenants' => $user->activeTenants->map(function ($tenant) {
                    return [
                        'id' => $tenant->id,
                        'uuid' => $tenant->uuid,
                        'company_name' => $tenant->company_name,
                        'role' => $tenant->pivot->role,
                        'status' => $tenant->status,
                    ];
                }),
            ],
            'message' => 'Perfil obtenido',
            'status' => 200,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'data' => null,
            'message' => __($status === Password::RESET_LINK_SENT ? 'Email enviado' : 'Error al enviar email'),
            'status' => $status === Password::RESET_LINK_SENT ? 200 : 400,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return response()->json([
            'data' => null,
            'message' => __($status === Password::PASSWORD_RESET ? 'Contraseña restablecida' : 'Error'),
            'status' => $status === Password::PASSWORD_RESET ? 200 : 400,
        ]);
    }
}
