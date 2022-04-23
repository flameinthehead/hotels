<?php

namespace App\Models;

class OstrovokCity extends City
{
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
