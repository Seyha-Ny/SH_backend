<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\SocialLoginAction;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnop")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnop")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Unified authentication: the same storefront form signs in both
        // customers and admins. We ALSO log the user into the web guard (when
        // a session exists) so admins can open the /admin panel directly
        // afterwards without a separate login page. Pure API clients (no
        // session) are unaffected — they keep using the bearer token only.
        $this->signInWebSessionIfAdmin($user, $request);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Establish the web session for admins signed in through the storefront
     * form, so the /admin panel recognizes them without a separate login.
     */
    private function signInWebSessionIfAdmin(User $user, Request $request): void
    {
        if ($user->is_admin
            && in_array($user->role, ['admin', 'super_admin'], true)
            && $request->hasSession()
        ) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the real bearer token when one was sent. The Sanctum guard
        // prefers the web session (unified login) over the bearer token, in
        // which case currentAccessToken() is a TransientToken that must not be
        // deleted — so look the token up directly instead. A missing or stale
        // token is simply a no-op.
        if ($token = $request->bearerToken()) {
            PersonalAccessToken::findToken($token)?->delete();
        }

        // Mirror the unified login: also end the web session (admin panel
        // access) when one was established by the same storefront form. Safe
        // to run even when the session is already gone (expired or already
        // logged out) — logout must always succeed gracefully.
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="The full authenticated user model (same shape as the login response)",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="is_admin", type="boolean", example=false),
     *             @OA\Property(property="role", type="string", example="customer"),
     *             @OA\Property(property="avatar", type="string", nullable=true),
     *             @OA\Property(property="telegram_chat_id", type="string", nullable=true),
     *             @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-01-01T00:00:00Z", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function user(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Return the full user model (same shape as the login/register
        // response) so the storefront keeps fields like avatar and
        // telegram_chat_id when fetchUser() refreshes the session.
        return response()->json($user);
    }

    public function socialCallback(Request $request): JsonResponse
    {
        try {
            $data = (new SocialLoginAction())->handle($request);

            // Unified authentication: social login goes through the same
            // storefront form, so admins get the web session too.
            $user = User::find($data['user']['id'] ?? null);
            if ($user) {
                $this->signInWebSessionIfAdmin($user, $request);
            }

            return response()->json([
                'token' => $data['token'],
                'user' => $data['user'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
