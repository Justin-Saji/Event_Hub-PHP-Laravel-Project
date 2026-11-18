<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'event_id',
    ];

    /**
     * Get the user account associated with the registration (nullable for guests).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event that the registration belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

