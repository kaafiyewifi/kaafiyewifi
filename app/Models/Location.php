<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    protected $fillable = ['name','city','address'];

    public function hotspots(){
        return $this->hasMany(Hotspot::class);
    }
}

