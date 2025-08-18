<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    // No timestamps on this table per schema

    /**
     * Get the vendors with this status.
     */
    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    /**
     * Get the users with this status.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}