<?php

namespace App\UseCase\Search;

use App\Models\City;
use Illuminate\Support\Carbon;

class Params
{
    private City $city;

    private Carbon $checkInDate;

    private Carbon $checkOutDate;

    private int $adults;

    public function getCity(): City
    {
        return $this->city;
    }

    public function getCheckInDate(): Carbon
    {
        return $this->checkInDate;
    }

    public function getCheckOutDate(): Carbon
    {
        return $this->checkOutDate;
    }

    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @param City $city
     */
    public function setCity(City $city): void
    {
        $this->city = $city;
    }

    /**
     * @param Carbon $checkInDate
     */
    public function setCheckInDate(Carbon $checkInDate): void
    {
        $this->checkInDate = $checkInDate;
    }

    /**
     * @param Carbon $checkOutDate
     */
    public function setCheckOutDate(Carbon $checkOutDate): void
    {
        $this->checkOutDate = $checkOutDate;
    }

    /**
     * @param int $adults
     */
    public function setAdults(int $adults): void
    {
        $this->adults = $adults;
    }
}
