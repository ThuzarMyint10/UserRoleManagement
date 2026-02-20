<?php

namespace App\Http\Contracts;

use Illuminate\Http\Request;


interface AuthInterface
{
    public function register(array $data): array;
    public function login(string $email, string $password): array;
    public function refresh(string $refreshToken): array;
    public function logout(Request $user): void;
}
