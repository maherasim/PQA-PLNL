<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'oauth_access_tokens';

    protected $fillable = [
        'user_id', 'client_id', 'name', 'scopes', 'revoked', 'expires_at'
    ];

    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
