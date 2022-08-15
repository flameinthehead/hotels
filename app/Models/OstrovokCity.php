<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OstrovokCity extends City
{
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }
}
