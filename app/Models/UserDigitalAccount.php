<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDigitalAccount extends Model
{
    protected $fillable = ['user_id', 'digital_account_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
