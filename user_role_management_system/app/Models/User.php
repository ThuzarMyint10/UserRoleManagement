<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; 
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [ 
        'name', 
        'email', 
        'password', 
        'phone', 
        'avatar', // profile picture path 
        'bio', // short description 
        'is_active', // account status 
        'refresh_token', // API access token refresh
        ];

    /** 
     * The attributes that should be hidden for arrays. 
     */ 
    protected $hidden = [ 
        'password', 
        'remember_token', 
        'refresh_token', // keep it secret in API responses
        ]; 

    /** 
     * The attributes that should be cast. 
     */ 
    protected $casts = [ 
        'email_verified_at' => 'datetime', 
        'is_active' => 'boolean', // ensure boolean casting 
    ];

    public function address()
    {
        return $this->hasMany(Address::class);
    }
}
