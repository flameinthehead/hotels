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
    private float $distanceToCenter;

    // Превью отеля
    private string $hotelPreview;

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

    public function getDistanceToCenter(): float
    {
        return $this->distanceToCenter;
    }

    public function setDistanceToCenter(float $distanceToCenter): void
    {
        $this->distanceToCenter = $distanceToCenter;
    }

    public function getHotelPreview(): string
    {
        return $this->hotelPreview;
    }

    public function setHotelPreview(string $hotelPreview): void
    {
        $this->hotelPreview = $hotelPreview;
    }
}
