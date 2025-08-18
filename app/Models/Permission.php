<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    public $timestamps = true;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'permissions_name',
        'modules_auth',
    ];

    protected $casts = [
        'modules_auth' => 'array',
    ];
}

