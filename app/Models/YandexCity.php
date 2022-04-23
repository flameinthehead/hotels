<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YandexCity extends Model
{
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
