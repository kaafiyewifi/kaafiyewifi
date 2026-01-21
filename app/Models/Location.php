<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    protected $fillable = [
        'name', 'status', 'address', 'city',
        // code is auto
    ];

    protected static function booted(): void
    {
        static::creating(function (Location $location) {
            if (!empty($location->code)) return;

            // Safe sequential code with transaction lock
            DB::transaction(function () use ($location) {
                // lock the locations table rows for consistent next number
                $last = DB::table('locations')
                    ->select('code')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                $nextNumber = 1;

                if ($last && isset($last->code)) {
                    // expect "kw001"
                    $digits = (int) preg_replace('/\D+/', '', $last->code);
                    $nextNumber = max(1, $digits + 1);
                }

                $location->code = 'kw' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            });
        });
    }
}
