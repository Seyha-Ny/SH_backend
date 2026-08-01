<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialLoginAction
{
    public function handle(Request $request): array
    {
        $request->validate([
            'provider' => 'required|string|in:google',
            'token' => 'required|string',
        ]);

        $provider = $request->input('provider');

        if ($provider === 'google') {
            $profile = $this->verifyGoogleToken($request->input('token'));
            $user = $this->findOrCreateUser($profile);
        } else {
            throw new \InvalidArgumentException('Unsupported social provider.');
        }

        $token = $user->createToken('social_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
                'role' => $user->role,
            ],
        ];
    }

    private function verifyGoogleToken(string $token): object
    {
        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $token,
        ]);

        if ($response->failed()) {
            Log::warning('Social login failed: Google token validation error', ['status' => $response->status()]);

            throw new \RuntimeException('Invalid social token.');
        }

        $data = $response->json();

        if (!empty($data['error'])) {
            Log::warning('Social login failed: Google tokeninfo returned error.', ['data' => $data]);

            throw new \RuntimeException($data['error_description'] ?? 'Invalid social token.');
        }

        $email = $data['email'] ?? null;
        $name = $data['name'] ?? ($data['email'] ?? 'User');

        if (!$email) {
            throw new \RuntimeException('Email is required from social provider.');
        }

        return (object) [
            'id' => $data['sub'] ?? Str::random(16),
            'name' => $name,
            'email' => $email,
            'token' => $token,
        ];
    }

    private function findOrCreateUser(object $profile): User
    {
        $user = User::where('email', $profile->email)->first();

        if ($user) {
            return $user;
        }

        return User::create([
            'name' => $profile->name,
            'email' => $profile->email,
            'password' => bcrypt(Str::random(32)),
            'is_admin' => false,
            'role' => null,
            'email_verified_at' => now(),
        ]);
    }
}
