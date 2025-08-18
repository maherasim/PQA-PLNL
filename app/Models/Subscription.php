<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'subscription_period_start',
        'subscription_period_end',
        'amount',
        'currency',
        'trial_ends_at',
        'created_by',
        'updated_by',
        'canceled_by',
        'created_at',
        'updated_at',
        'canceled_at',
    ];

    protected $casts = [
        'subscription_period_start' => 'datetime',
        'subscription_period_end' => 'datetime',
        'amount' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];
}

