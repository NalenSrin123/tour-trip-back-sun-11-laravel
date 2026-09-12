<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Guide extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'guide_id';

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // Mutator to hash password automatically when creating/updating
    public function setPasswordHashAttribute(string $value)
    {
        $this->attributes['password_hash'] = bcrypt($value);
    }
}
