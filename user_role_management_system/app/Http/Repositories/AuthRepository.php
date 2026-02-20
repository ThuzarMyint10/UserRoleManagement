<?php
namespace App\Http\Repositories;

use App\Models\User;

class AuthRepository
{
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByRefreshToken(string $token): ?User
    {
        return User::where('refresh_token', $token)->first();
    }

    public function updateRefreshToken(User $user, ?string $token): void
    {
        $user->forceFill(['refresh_token' => $token])->save();
    }
}

