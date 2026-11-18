<?php

namespace App\Models;

use App\Mail\PasswordReset;
use App\Mail\Welcome;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'role'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = ['email_verified_at' => 'datetime'];

    /**
     * Send the welcome email
     *
     * @return void
     */
    public function sendWelcomeNotification() {
        Mail::to($this->email)->queue(new Welcome($this));
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token) {
        Mail::to($this->email)->queue(new PasswordReset($this, $token));
    }

    /**
     * Check if user is of given role
     *
     * @param $role
     * @return mixed
     */
    public function isRole($role) {
        return is_array($role) ? in_array($this->role, $role) : $this->role == $role;
    }
}
