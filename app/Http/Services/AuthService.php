<?php

namespace App\Http\Services;

use App\Http\Contracts\AuthInterface;
use App\Http\Repositories\AuthRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Exception;
use App\Jobs\SendEmailVerificationJob;

class AuthService implements AuthInterface
{
    public function __construct(
        private AuthRepository $authRepository
    ) {}

    public function register(array $data): array
    {
        $user = $this->authRepository->createUser([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // must use Event
        // $user->sendEmailVerificationNotification();

        // Dispatch to queue instead of sending immediately
        SendEmailVerificationJob::dispatch($user);

        return $this->generateAuthPayload($user);
    }

    public function login(string $email, string $password): array
    { 
        $user = $this->authRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            $this->throwAuthException('Invalid credentials');
        }

        if (!$user->hasVerifiedEmail()) {
            $this->throwAuthException('Please verify your email before logging in.');
        }

        return $this->generateAuthPayload($user);
    }

    public function refresh(string $refreshToken): array
    {
        $user = $this->authRepository->findByRefreshToken($refreshToken);

        if (!$user) {
            $this->throwAuthException('Invalid refresh token');
        }

        return $this->generateAuthPayload($user, true);
    }

    public function logout(Request $request): void
    {
        $user = $request->user();

        $user->currentAccessToken()?->delete();
        $this->authRepository->updateRefreshToken($user, null);
    }

    /**
     * Generate authentication payload with access and refresh tokens.
     */
    private function generateAuthPayload(User $user, bool $rotateRefreshToken = true): array
    {
        $accessToken = $user->createToken('access_token')->plainTextToken;

        $refreshToken = $rotateRefreshToken
            ? $this->rotateRefreshToken($user)
            : $user->refresh_token;

        return [
            'user'          => $user,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 3600,
        ];
    }

    /**
     * Rotate and persist a new refresh token.
     */
    private function rotateRefreshToken(User $user): string
    {
        $refreshToken = Str::random(128);
        $this->authRepository->updateRefreshToken($user, $refreshToken);

        return $refreshToken;
    }

    /**
     * Centralized exception throwing for authentication errors.
     */
    private function throwAuthException(string $message): void
    {
        throw new Exception($message);
    }
}
