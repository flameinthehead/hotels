<?php

namespace App\UseCase\Search;

class Result
{
    // Название отеля
    private string $name;

    // Стоимость за всё время пребывания
    private int $price;

    // Ссылка на страницу бронирования
    private string $bookLink;

    // Удобства
    private array $facilities;

    // Расстояние до центра
    private float|null $distanceToCenter = null;

    // Превью отеля
    private string|null $hotelPreview = null;

    // Источник поиска
    private string $ref;

    // Широта отеля
    private float $latitude;

    // Долгота отеля
    private float $longitude;

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
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
        $this->name = $name;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getBookLink(): string
    {
        return $this->bookLink;
    }

    public function setBookLink(string $bookLink): void
    {
        $this->bookLink = $bookLink;
    }

    public function getFacilities(): array
    {
        return $this->facilities;
    }

    public function setFacilities(array $facilities): void
    {
        $this->facilities = $facilities;
    }

    public function getDistanceToCenter(): mixed
    {
        return $this->distanceToCenter;
    }

    public function setDistanceToCenter(float $distanceToCenter): void
    {
        $this->distanceToCenter = $distanceToCenter;
    }

    public function getHotelPreview(): string|null
    {
        return $this->hotelPreview;
    }

    public function setHotelPreview(string|null $hotelPreview): void
    {
        $this->hotelPreview = $hotelPreview;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function setRef(string $ref): void
    {
        $this->ref = $ref;
    }
}
