<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authenticator extends Model
{
    use HasFactory;

    protected $fillable = [
        'credential_id',
        'public_key',
    ];

    protected $casts = [
        'credential_id' => 'encrypted',
        'public_key'    => 'encrypted:json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function credentialId(): Attribute
    {
        return new Attribute(
            get: fn ($value) => base64_decode($value),
            set: fn ($value) => base64_encode($value),
        );
    }
}
