<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasFactory;

    protected $primaryKey = 'destination_id';

    protected $fillable = [
        'destination_name',
    ];

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class, 'destination_id', 'destination_id');
    }
}