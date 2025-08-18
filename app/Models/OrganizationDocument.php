<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationDocument extends Model
{
    use HasFactory;

    protected $table = 'organization_documents';

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'status',
        'document_type',
        'name',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'checksum',
        'uploaded_by',
        'reviewed_by',
        'expires_at',
        'is_required',
        'is_shareable',
        'version',
        'previous_version_id',
        'rejection_reason',
        'reviewed_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_shareable' => 'boolean',
        'expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(OrganizationDocument::class, 'previous_version_id');
    }
}

