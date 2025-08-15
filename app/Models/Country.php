<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'currency_code',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'phone_code' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vendors from this country.
     */
    public function vendors()
    {
        return $this->hasMany(Vendor::class, 'country_id');
    }

    /**
     * Get the vendors with this phone country.
     */
    public function phoneVendors()
    {
        return $this->hasMany(Vendor::class, 'phone_country_id');
    }

    /**
     * Get the users from this country.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'user_country_id');
    }

    /**
     * Get the users with this mobile country.
     */
    public function mobileUsers()
    {
        return $this->hasMany(User::class, 'mobile_country_id');
    }
}