<?php

namespace Noorfarooqy\Salaamch\DataModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchPartnerUser extends Model
{

    use HasFactory;
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i',
        'reversed_at' => 'date:Y-m-d H:i',
    ];

    protected $guarded = [];
}
