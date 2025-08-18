<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesPermission extends Model
{
    use HasFactory;

    protected $table = 'roles_permissions';

    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'role_id',
        'permissions_id',
        'status',
        'created_by',
        'updated_by',
        'canceled_by',
        'created_at',
        'updated_at',
        'canceled_at',
    ];
}

