<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * | Get User by Email
     */
    public function getUserByEmail($email)
    {
        return User::where('email', $email)
            ->first();
    }

    public function sendPasswordResetNotification($token)
    {
        $url = 'localhost/reset-password?token' . $token;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function updateUserDetail($request)
    {
        $refValues = User::where('id', $request->id)->first();
        User::where('id', $request->id)
            ->update(
                [
                    'name'    => $request->name   ?? $refValues->name,
                    'email'   => $request->email  ?? $refValues->email,
                    'mobile'  => $request->mobile ?? $refValues->mobile
                ]
            );
    }
}
