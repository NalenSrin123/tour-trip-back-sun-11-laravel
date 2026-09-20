<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTraveler extends Model
{
    //
    use  HasFactory;
    protected $table = 'booking_travelers';
    protected $primaryKey = 'traveler_id';

    protected $fillable = [
        'booking_id',
        'full_name',
        
    ];
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
