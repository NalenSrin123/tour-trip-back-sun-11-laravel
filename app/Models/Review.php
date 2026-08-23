<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    // Override the default 'id' primary key
    protected $primaryKey = 'review_id';

    protected $fillable = [
        'tour_id',
        'user_id',
        'booking_id',
        'rating',
        'comment',
        'is_approved',
    ];

    /**
     * Get the tour that was reviewed.
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the specific booking associated with this review.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}