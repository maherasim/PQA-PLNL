<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase;

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

    // Make sure the base model doesn't expect the `data` attribute
    public $data = [];

    /**
     * Remove the "data" column when converting to array (e.g., for DB insert/update).
     */
    public function toArray()
    {
        $array = parent::toArray();
        unset($array['data']);
        return $array;
    }

    /**
     * Remove `data` from being saved during create/update operations.
     */
    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            unset($tenant->data);
        });

        static::updating(function (Tenant $tenant) {
            unset($tenant->data);
        });
    }

    /**
     * Used by stancl/tenancy for identifying the DB name.
     */
    public function getDatabaseName(): ?string
    {
        return $this->db_name;
    }

    public function setDatabaseName(string $name): void
    {
        $this->db_name = $name;
    }

    /**
     * Optional: you can map tenancy to use a custom key.
     */
    public function getTenantKeyName(): string
    {
        return 'id'; // UUID assumed
    }

    /**
     * Define the custom columns that should be preserved by stancl/tenancy.
     */
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
    
    /**
     * Get the domains for the tenant.
     */
    public function domains()
    {
        // Return an empty relationship since we're storing domain directly in the tenants table
        // and not using a separate domains table
        return $this->hasMany(\App\Models\Tenant::class)->where('id', '<', 0); // Always empty
    }
}
