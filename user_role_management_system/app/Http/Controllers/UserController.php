<?php

namespace App\Http\Controllers;

use App\Http\Services\UserService;
use App\Http\Resources\V1\UserResource;

class UserController extends BaseController
{
    public function __construct(UserService $service)
    {
        // Pass allowed includes for User
        parent::__construct($service, UserResource::class, ['address']);
    }
}
