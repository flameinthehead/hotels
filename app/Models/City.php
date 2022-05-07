<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class City extends Model
{
    use HasFactory;

    public function yandexCity(): HasOne
    {
        return $this->hasOne(YandexCity::class);
    }

    public function ostrovokCity(): HasOne
    {
        return $this->hasOne(OstrovokCity::class);
    }

    public function findByName(string $name): self|null
    {
        return self::query()->where('name', $name)->get()->first();
    }
}
