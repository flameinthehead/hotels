<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use ZeroDaHero\LaravelWorkflow\Traits\WorkflowTrait;

class TelegramRequest extends Model
{
    use WorkflowTrait;

    public const STATUS_NEW = 'new';
    public const STATUS_CITY = 'city';
    public const STATUS_CHECK_IN = 'check_in';
    public const STATUS_CHECK_OUT = 'check_out';
    public const STATUS_ADULTS = 'adults';

    protected $guarded = ['id'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function findNotFinishedByUserId(int $telegramUserId): ?self
    {
        return self::query()->notFinished()->where('telegram_from_id', $telegramUserId)->first();
    }

    public function scopeNotFinished(Builder $builder): Builder
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

    public function getCity(): City
    {
        return $this->city;
    }

    public function getCheckInDate(): string
    {
        return $this->check_in;
    }

    public function getCheckOutDate(): string
    {
        return $this->check_out;
    }

    public function setIsFinished(bool $isFinished): void
    {
        $this->is_finished = $isFinished;
    }
}
