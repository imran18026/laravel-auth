<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;
// use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register a new user with 'user' role
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        $user->assignRole('user');

        // $user->sendEmailVerificationNotification();

        DB::commit();
        return response()->json([
            'message' => 'User registered successfully. Please verify your email.',
            'user' => $user->only(['id', 'name', 'email']),
            'token'=>$token,
        ], 201);

        } catch (JWTException $e) {
           DB::rollBack();
            return response()->json(['error' => 'Could not create token'], 500);
        }

    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = JWTAuth::user();

            $coustomInfo=[
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ];
            $token = JWTAuth::claims($coustomInfo)->fromUser($user );


        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        $refreshToken = JWTAuth::claims(['type' => 'refresh'])->fromUser(auth()->user());

        return response()->json([
            'accessToken' => $token,
            'refreshToken' => $refreshToken,

        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['error' => __($status)], 400);
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
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['error' => __($status)], 400);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['error' => 'Invalid verification link'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified successfully']);
    }

    public function resendEmailVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->firstOrFail();
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent']);
    }

    // public function sendSmsCode(Request $request)
    // {
    //     $request->validate(['phone' => 'required|string']);

    //     // Simulated response. Integrate real SMS service.
    //     return response()->json([
    //         'message' => 'SMS verification code sent',
    //         'phone' => $request->phone
    //     ]);
    // }

    // public function verifySmsCode(Request $request)
    // {
    //     $request->validate([
    //         'phone' => 'required|string',
    //         'code' => 'required|string'
    //     ]);

    //     $user = User::where('phone', $request->phone)->firstOrFail();
    //     $user->update(['phone_verified' => true]);

    //     return response()->json([
    //         'message' => 'Phone number verified',
    //         'phone' => $request->phone
    //     ]);
    // }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    // public function redirectToProvider($provider)
    // {
    //     return Socialite::driver($provider)->stateless()->redirect();
    // }

    // public function handleProviderCallback($provider)
    // {
    //     try {
    //         $socialUser = Socialite::driver($provider)->stateless()->user();

    //         $user = User::updateOrCreate(
    //             ['email' => $socialUser->getEmail()],
    //             [
    //                 'name' => $socialUser->getName(),
    //                 'password' => Hash::make(Str::random(24)),
    //                 'email_verified_at' => now(),
    //             ]
    //         );

    //         $user->assignRole('user');
    //         $token = JWTAuth::fromUser($user);

    //         return $this->respondWithToken($token);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'OAuth authentication failed'], 401);
    //     }
    // }

    public function me()
    {
        $user = JWTAuth::user()->load('roles');
        return response()->json([
            'user' => $user,
            'roles' => $user->roles->pluck('name'),
            'is_admin' => $user->isAdmin(),
            'is_super_admin' => $user->isSuperAdmin()
        ]);
    }

    public function logout()
    {

        try {
            // Invalidate the token
            JWTAuth::parseToken()->invalidate();
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, please try again'], 500);
        }

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        $user = JWTAuth::user();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $user->only(['id', 'name', 'email']),
            'roles' => $user->roles->pluck('name'),
            'is_admin' => $user->isAdmin(),
            'is_super_admin' => $user->isSuperAdmin()
        ]);
    }
}
