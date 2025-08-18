<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyCategory extends Model
{
    use HasFactory;

    protected $table = 'supply_categories';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'parent_id',
        'level',
        'is_active',
        'requires_special_qualification',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_special_qualification' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SupplyCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(SupplyCategory::class, 'parent_id');
    }
}

