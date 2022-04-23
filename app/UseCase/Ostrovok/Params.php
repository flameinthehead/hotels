<?php

namespace App\UseCase\Ostrovok;

use App\UseCase\Search\SearchParamsFactoryInterface;

class   Params implements SearchParamsFactoryInterface
{
    private string $departureDate;

    private string $arrivalDate;

    private int $regionId;

    private int $adults;

    public function getDepartureDate(): string
    {
        return $this->departureDate;
    }

    public function setDepartureDate(string $departureDate): void
    {
        $this->departureDate = $departureDate;
    }

    public function getArrivalDate(): string
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(string $arrivalDate): void
    {
        $this->arrivalDate = $arrivalDate;
    }

    public function getRegionId(): int
    {
        return $this->regionId;
    }

    public function setRegionId(int $regionId): void
    {
        $this->regionId = $regionId;
    }

    public function getAdults(): int
    {
        return $this->adults;
    }

    public function setAdults(int $adults): void
    {
        $this->adults = $adults;
    }

    public static function makeSourceParams(\App\UseCase\Search\Params $generalParams): self
    {
        $params = new self();
        $params->setDepartureDate($generalParams->getCheckOutDate()->format('Y-m-d'));
        $params->setArrivalDate($generalParams->getCheckInDate()->format('Y-m-d'));
        $params->setAdults($generalParams->getAdults());
        $params->setRegionId($generalParams->getCity()->ostrovokCity()->first()->ostrovok_city_id);
        return $params;
    }
}
