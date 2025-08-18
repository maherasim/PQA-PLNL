<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory;
    use HasDatabase;

    protected $fillable = [
        'organization_id',
        'status',
        'domain',
        'db_address',
        'db_name',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Disable stancl default data casting/column expectations
    protected $castsForJson = [];
    protected $guarded = ['data'];
    protected $attributes = [];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (isset($tenant->attributes['data'])) {
                unset($tenant->attributes['data']);
            }
        });
        static::updating(function (Tenant $tenant) {
            if (isset($tenant->attributes['data'])) {
                unset($tenant->attributes['data']);
            }
        });
    }

    // Map your existing `database` column to tenancy's database name
    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getDatabaseName(): ?string
    {
        return $this->db_name;
    }

    public function setDatabaseName(string $name): void
    {
        $this->db_name = $name;
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'organization_id',
            'status',
            'domain',
            'db_address',
            'db_name',
            'created_at',
            'updated_at',
            'deleted_at',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }
}
