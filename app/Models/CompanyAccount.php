<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAccount extends Model
{
    protected $fillable = [
        'remote_id',
        'uuid',
        'agencyNumber',
        'accountNumber',
        'active',
        'snapshot',
    ];

    protected $casts = [
        'active'   => 'boolean',
        'snapshot' => 'array',
    ];
}
