<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory;
    use HasDatabase;
    use HasDomains;

    protected $fillable = [
        'name',
        'database',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Map your existing `database` column to tenancy's database name
    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getDatabaseName(): ?string
    {
        return $this->database;
    }

    public function setDatabaseName(string $name): void
    {
        $this->database = $name;
    }
}
