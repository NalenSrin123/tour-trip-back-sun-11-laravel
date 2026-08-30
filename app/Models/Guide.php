<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guide extends Model
{
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

    public function tourSchedules(): HasMany
    {
        return $this->hasMany(
            TourSchedule::class,
            'guide_id',
            'guide_id'
        );
    }
}