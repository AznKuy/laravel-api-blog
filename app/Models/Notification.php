<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'is_read',

    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
