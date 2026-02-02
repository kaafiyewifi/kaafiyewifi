<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterCredential extends Model
{
    protected $table = 'router_credentials';

    protected $fillable = [
        'router_id',
        'auth_type',
        'username',
        'password_encrypted',
    ];

    protected $casts = [
        'router_id' => 'integer',
        // ✅ auto encrypt/decrypt
        'password_encrypted' => 'encrypted',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'router_id');
    }

    // ✅ Optional helper accessor
    public function getPasswordAttribute(): ?string
    {
        return $this->password_encrypted; // decrypted automatically
    }

    public function setPasswordAttribute(?string $value): void
    {
        $this->password_encrypted = $value; // encrypted automatically
    }
}
