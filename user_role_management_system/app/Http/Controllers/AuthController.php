<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Contracts\AuthInterface;
use App\Http\Resources\V1\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    public function __construct(
        private AuthInterface $authService
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $result = $this->authService->register($data); 

        return $this->mapToAuthResource($result, $request);;
    }

    

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified']);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json(['message' => 'Email verified successfully']);
    }

    public function login(Request $request)
    {
        $data = $request->validate(
            [
                'email' => 'required|email:rfc,dns|max:255',
                'password' => 'required|string|min:8',
            ],
            [
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.min' => 'Password must be at least 8 characters.',
            ]
        );

        $result = $this->authService->login(
            $data['email'],
            $data['password']
        );

        return $this->mapToAuthResource($result, $request);
    }

    public function refresh(Request $request)
    {
        $data = $request->validate([
            'refresh_token' => 'required|string|size:128',
        ]);

        $result = $this->authService->refresh($data['refresh_token']);

        return $this->mapToAuthResource($result, $request);;
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return response()->json(['message' => 'Logged out successfully']);
    }

    private function mapToAuthResource(array $data, Request $request): AuthResource
    {
        $user = $data['user'];

        /**
         * Handle dynamic includes (e.g. ?include=address)
         */
        if ($request->filled('include')) {

            $requestedIncludes = explode(',', $request->query('include')); //['address'];

            // Whitelist allowed relationships (security best practice)
            $allowedIncludes = ['address']; 

            $validIncludes = array_intersect($requestedIncludes, $allowedIncludes);

            if (!empty($validIncludes)) {
                $user->load($validIncludes);
            }
        }

        /**
         * Attach token data (runtime attributes)
         */
        $user->access_token  = $data['access_token'] ?? null;
        $user->refresh_token = $data['refresh_token'] ?? null;
        $user->token_type    = $data['token_type'] ?? 'Bearer';
        $user->expires_in    = $data['expires_in'] ?? 3600;

        return new AuthResource($user);
    }

}


