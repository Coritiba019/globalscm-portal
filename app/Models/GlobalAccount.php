<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalAccount extends Model
{
    protected $fillable = [
        'user_id',
        'account_number',
        'digital_account_id',
        'agency',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
