<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Оконченный запрос (когда выведены результаты поиска)
     * @var bool
     */
    private bool $isFinished;

    /**
     * Последнее сообщение, отправленное боту от пользователя
     * @var string
     */
    private string $lastMessage;

    /**
     * Id пользователя в ТГ
     * @var int
     */
    private int $telegramFromId;

    protected $guarded = ['id'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function findNotFinishedByUserId(int $telegramUserId): ?self
    {
        return self::query()->notFinished()->where('telegram_from_id', $telegramUserId)->first();
    }

    public function scopeNotFinished($builder)
    {
        return $builder->where('is_finished', '0');
    }

    public function setLastMessage(string $lastMessage): void
    {
        $this->attributes['last_message'] = $lastMessage;
    }

    public function getLastMessage(): string
    {
        return $this->last_message;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setCheckInDate(\DateTime $checkInDate): void
    {
        $this->attributes['check_in'] = $checkInDate;
    }

    public function setCheckOutDate(\DateTime $checkOutDate): void
    {
        $this->attributes['check_out'] = $checkOutDate;
    }

    protected function isFinished()
    {
        return Attribute::make(
            get: fn($value) => (bool)$value
        );
    }

    public function setAdults(int $adults): void
    {
        $this->adults = $adults;
    }

    public function getAdults(): int
    {
        return $this->adults;
    }
}
