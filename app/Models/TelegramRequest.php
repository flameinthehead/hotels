<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ZeroDaHero\LaravelWorkflow\Traits\WorkflowTrait;

class TelegramRequest extends Model
{
    use WorkflowTrait;

    public const STATUS_NEW = 'new';
    public const STATUS_CITY = 'city';
    public const STATUS_CHECK_IN = 'check_in';
    public const STATUS_CHECK_OUT = 'check_out';
    public const STATUS_ADULTS = 'adults';


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

    /**
     * Id пользователя в ТГ
     * @var int
     */
    private int $telegramFromId;

    protected $guarded = ['id'];

    private function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function findNotFinishedByUserId(int $telegramUserId)
    {
        return self::query()->notFinished()->where('telegram_from_id', $telegramUserId)->first();
    }

    public function scopeNotFinished($builder)
    {
        return $builder->where('is_finished', '0');
    }
}
