<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;

class Payment extends Model
{
    //
    protected $primaryKey = 'payment_id';
    protected $fillable = [
        'booking_id',
        'transaction_ref',
        'payment_method',
        'amount',
        'status',
        'paid_at',
    ];
    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
