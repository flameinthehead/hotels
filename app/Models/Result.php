<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $table = 'search_results';

    protected $dates = [
        'created_at',
        'updated_at',
        'check_in_date',
        'check_out_date',
    ];

    public function searchRequest(): BelongsTo
    {
        $this->belongsTo(SearchRequest::class);
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->attributes['latitude'] = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->attributes['longitude'] = $longitude;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->attributes['name'] = $name;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->attributes['price'] = $price;
    }

    public function getBookLink(): string
    {
        return $this->book_link;
    }

    public function setBookLink(string $bookLink): void
    {
        $this->attributes['book_link'] = $bookLink;
    }

    public function getFacilities(): string|null
    {
        return $this->facilities;
    }

    public function setFacilities(array $facilities): void
    {
        $this->attributes['facilities'] = implode(', ', $facilities);
    }

    public function getDistanceToCenter(): mixed
    {
        return $this->distance_to_center;
    }

    public function setDistanceToCenter(float $distanceToCenter): void
    {
        $this->attributes['distance_to_center'] = $distanceToCenter;
    }

    public function getHotelPreview(): string|null
    {
        return $this->preview;
    }

    public function setHotelPreview(string|null $hotelPreview): void
    {
        $this->attributes['preview'] = $hotelPreview;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function setRef(string $ref): void
    {
        $this->attributes['ref'] = $ref;
    }

    public function getCheckInDate(): \DateTime
    {
        return $this->check_in_date;
    }

    /**
     * @param \DateTime $checkInDate
     */
    public function setCheckInDate(\DateTime $checkInDate): void
    {
        $this->attributes['check_in_date'] = $checkInDate;
    }

    public function getCheckOutDate(): \DateTime
    {
        return $this->check_out_date;
    }

    public function setCheckOutDate(\DateTime $checkOutDate): void
    {
        $this->attributes['check_out_date'] = $checkOutDate;
    }

    public function setAddress(string $address): void
    {
        $this->attributes['address'] = $address;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setStars(?int $stars): void
    {
        $this->attributes['stars'] = $stars;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }
}
