<?php

namespace Noorfarooqy\Salaamch\DataModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchPartner extends Model
{

    use HasFactory;
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i',
        'updated_at' => 'date:Y-m-d H:i',
    ];

    protected $guarded = [];
}
