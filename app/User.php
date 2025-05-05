<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'password',
        'image',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getFullNameAttribute()
    {
        return ucfirst($this->f_name) . ' ' . ucfirst($this->l_name);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function customOrders()
    {
        return $this->hasMany(CustomOrder::class);
    }
}
