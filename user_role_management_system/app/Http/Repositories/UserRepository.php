<?php

namespace App\Http\Repositories;

use App\Models\User;
use App\Http\Contracts\UserInterface;

class UserRepository extends BaseRepository implements UserInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    // Add user-specific queries if needed
}
