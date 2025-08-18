<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOrganizationRoleMapping extends Model
{
    use HasFactory;

    protected $table = 'user_organization_role_mapping';

    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'user_id',
        'role_id',
        'status',
        'start_date',
        'end_date',
        'created_at',
        'granted_at',
        'updated_at',
        'created_by',
        'granted_by',
        'updated_by',
    ];
}

