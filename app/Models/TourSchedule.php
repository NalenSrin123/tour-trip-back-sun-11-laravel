<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourSchedule extends Model
{
    protected $table = 'tour_schedule';

    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'tour_id',
        'guide_id',
        'start_date',
        'end_date',
        'max_capacity',
        'booked_seats',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_capacity' => 'integer',
        'booked_seats' => 'integer',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(
            Tour::class,
            'tour_id',
            'id'
        );
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(
            Guide::class,
            'guide_id',
            'guide_id'
        );
    }
}