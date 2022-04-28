<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramRequest extends Model
{
    private int $id;

    /**
     * Город
     * @var City
     */
    private City $city;

    /**
     * Дата заезда
     * @var \DateTime
     */
    private \DateTime $checkIn;

    /**
     * Дата выезда
     * @var \DateTime
     */
    private \DateTime $checkOut;

    /**
     * Количество взрослых
     * @var int
     */
    private int $adults;

    private function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
