<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'address',
        'city',
        'code', // ✅ allow code to be set (factory/seed) if needed
    ];

    protected static function booted(): void
    {
        static::creating(function (Location $location) {
            // haddii code lagu soo diray (seed/factory/manual), ha badalin
            if (!empty($location->code)) return;

            DB::transaction(function () use ($location) {
                $last = DB::table('locations')
                    ->select('code')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                $nextNumber = 1;

                if ($last && isset($last->code)) {
                    $digits = (int) preg_replace('/\D+/', '', (string) $last->code);
                    $nextNumber = max(1, $digits + 1);
                }

                $location->code = 'kw' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            });
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
