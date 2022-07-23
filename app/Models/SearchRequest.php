<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchRequest extends Model
{
    public function searchResults(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function telegramRequest(): BelongsTo
    {
        return $this->belongsTo(TelegramRequest::class);
    }
}
