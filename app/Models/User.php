<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'email_verified_at',
        'full_name',
        'user_country_id',
        'mobile_country_id',
        'mobile_number',
        'status',
        'last_login_at',
        'cvb_id',
        'cvb_number',
        'password_hash',
        'password_created_at',
        'password_last_changed',
        'password_expires_at',
        'password_change_required',
        'failed_login_attempts',
        'last_failed_login',
        'account_locked_until',
        'last_successful_login',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'last_login_at' => 'datetime',
            'password_created_at' => 'datetime',
            'password_last_changed' => 'datetime',
            'password_expires_at' => 'datetime',
            'last_failed_login' => 'datetime',
            'account_locked_until' => 'datetime',
            'last_successful_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password_change_required' => 'boolean',
        ];
    }

    /**
     * Get the country of the user.
     */
    public function userCountry()
    {
        return $this->belongsTo(Country::class, 'user_country_id');
    }

    /**
     * Get the mobile country of the user.
     */
    public function mobileCountry()
    {
        return $this->belongsTo(Country::class, 'mobile_country_id');
    }

    /**
     * Get the status of the user.
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // No direct role foreign key on users; roles are mapped via user_organization_role_mapping

    /**
     * Get the user that created this user.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated this user.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted this user.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the vendors created by this user.
     */
    public function createdVendors()
    {
        return $this->hasMany(Vendor::class, 'created_by');
    }

    /**
     * Get the vendors updated by this user.
     */
    public function updatedVendors()
    {
        return $this->hasMany(Vendor::class, 'updated_by');
    }

    /**
     * Get the users created by this user.
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Get the users updated by this user.
     */
    public function updatedUsers()
    {
        return $this->hasMany(User::class, 'updated_by');
    }
}
