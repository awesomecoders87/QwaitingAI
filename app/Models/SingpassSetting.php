<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SingpassSetting extends Model
{
    use HasFactory;

    protected $table = 'singpass_settings';

    protected $fillable = [
        'team_id',
        'location_id',
        'client_id',
        'environment',
        'signing_private_key',
        'signing_public_key',
        'enc_private_key',
        'enc_public_key',
        'keys_generated_at',
        'is_enabled',
        'created_by',
    ];

    protected $casts = [
        'keys_generated_at' => 'datetime',
        'is_enabled'        => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Mutators — encrypt before saving
    // -------------------------------------------------------------------------

    public function setSigningPrivateKeyAttribute($value): void
    {
        $this->attributes['signing_private_key'] = !empty($value) ? Crypt::encryptString($value) : null;
    }

    public function setEncPrivateKeyAttribute($value): void
    {
        $this->attributes['enc_private_key'] = !empty($value) ? Crypt::encryptString($value) : null;
    }

    // -------------------------------------------------------------------------
    // Accessors — decrypt when reading
    // -------------------------------------------------------------------------

    public function getSigningPrivateKeyAttribute($value): ?string
    {
        if (empty($value)) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getEncPrivateKeyAttribute($value): ?string
    {
        if (empty($value)) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTeamLocation($query, $teamId, $locationId)
    {
        return $query->where('team_id', $teamId)->where('location_id', $locationId);
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function team()
    {
        return $this->belongsTo(Tenant::class, 'team_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
