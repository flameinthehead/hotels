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
    private array|null $facilities = null;

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

    // Дата заезда
    private \DateTime $checkInDate;

    // Дата выезда
    private \DateTime $checkOutDate;

    private string $address;

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

    public function getFacilities(): array|null
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

    public function getCheckInDate(): \DateTime
    {
        return $this->checkInDate;
    }

    /**
     * @param \DateTime $checkInDate
     */
    public function setCheckInDate(\DateTime $checkInDate): void
    {
        $this->checkInDate = $checkInDate;
    }

    public function getCheckOutDate(): \DateTime
    {
        return $this->checkOutDate;
    }

    public function setCheckOutDate(\DateTime $checkOutDate): void
    {
        $this->checkOutDate = $checkOutDate;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): string
    {
        return $this->address;
    }
}
