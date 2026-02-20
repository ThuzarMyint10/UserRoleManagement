<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user' => [
                'id'    => $this->id,
                'name'  => $this->name,
                'email' => $this->email,

                'address' => $this->whenLoaded('address', function () {
                    return [
                        'street'  => $this->address->street,
                        'city'    => $this->address->city,
                        'state'   => $this->address->state,
                        'zip'     => $this->address->zip,
                        'country' => $this->address->country,
                    ];
                }),
            ],

            'access_token'  => $this->access_token,
            'token_type'    => $this->token_type ?? 'Bearer',
            'expires_in'    => $this->expires_in ?? 3600,
            'refresh_token' => $this->refresh_token ?? null,
        ];
    }

    public function with($request)
    {
        return [
            'meta' => [
                'api_version' => 'v1',
                'timestamp'   => now()->toISOString(),
                'request_id'  => $request->header('X-Request-Id') ?? uniqid(),
            ],
        ];
    }
}
