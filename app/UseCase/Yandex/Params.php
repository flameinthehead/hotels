<?php

namespace App\UseCase\Yandex;

use App\UseCase\Search\SearchParamsFactoryInterface;
use Carbon\Carbon;

class Params implements SearchParamsFactoryInterface
{
    public const SORT_CHEAP_FIRST = 'cheap-first';

    private string $startSearchReason = 'sort';

    // количество отелей на странице
    private int $pageHotelCount = 25;

    private int $pricedHotelLimit = 26;

    /**
     * @return string
     */
    public function getStartSearchReason(): string
    {
        return $this->startSearchReason;
    }

    /**
     * @param string $startSearchReason
     */
    public function setStartSearchReason(string $startSearchReason): void
    {
        $this->startSearchReason = $startSearchReason;
    }

    /**
     * @return int
     */
    public function getPageHotelCount(): int
    {
        return $this->pageHotelCount;
    }

    /**
     * @param int $pageHotelCount
     */
    public function setPageHotelCount(int $pageHotelCount): void
    {
        $this->pageHotelCount = $pageHotelCount;
    }

    /**
     * @return int
     */
    public function getPricedHotelLimit(): int
    {
        return $this->pricedHotelLimit;
    }

    /**
     * @param int $pricedHotelLimit
     */
    public function setPricedHotelLimit(int $pricedHotelLimit): void
    {
        $this->pricedHotelLimit = $pricedHotelLimit;
    }

    /**
     * @return int
     */
    public function getTotalHotelLimit(): int
    {
        return $this->totalHotelLimit;
    }

    /**
     * @param int $totalHotelLimit
     */
    public function setTotalHotelLimit(int $totalHotelLimit): void
    {
        $this->totalHotelLimit = $totalHotelLimit;
    }

    /**
     * @return int
     */
    public function getPollIteration(): int
    {
        return $this->pollIteration;
    }

    /**
     * @param int $pollIteration
     */
    public function setPollIteration(int $pollIteration): void
    {
        $this->pollIteration = $pollIteration;
    }

    /**
     * @return int
     */
    public function getPollEpoch(): int
    {
        return $this->pollEpoch;
    }

    /**
     * @param int $pollEpoch
     */
    public function setPollEpoch(int $pollEpoch): void
    {
        $this->pollEpoch = $pollEpoch;
    }

    /**
     * @return string
     */
    public function getCheckInDate(): string
    {
        return $this->checkInDate;
    }

    /**
     * @param string $checkInDate
     */
    public function setCheckInDate(string $checkInDate): void
    {
        $this->checkInDate = $checkInDate;
    }

    /**
     * @return string
     */
    public function getCheckOutDate(): string
    {
        return $this->checkOutDate;
    }

    /**
     * @param string $checkOutDate
     */
    public function setCheckOutDate(string $checkOutDate): void
    {
        $this->checkOutDate = $checkOutDate;
    }

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     */
    public function setAdults(int $adults): void
    {
        $this->adults = $adults;
    }

    /**
     * @return int
     */
    public function getGeoId(): int
    {
        return $this->geoId;
    }

    /**
     * @param int $geoId
     */
    public function setGeoId(int $geoId): void
    {
        $this->geoId = $geoId;
    }

    /**
     * @return string
     */
    public function getSelectedSortId(): string
    {
        return $this->selectedSortId;
    }

    /**
     * @param string $selectedSortId
     */
    public function setSelectedSortId(string $selectedSortId): void
    {
        $this->selectedSortId = $selectedSortId;
    }

    /**
     * @return string
     */
    public function getGeoLocationStatus(): string
    {
        return $this->geoLocationStatus;
    }

    /**
     * @param string $geoLocationStatus
     */
    public function setGeoLocationStatus(string $geoLocationStatus): void
    {
        $this->geoLocationStatus = $geoLocationStatus;
    }

    private int $totalHotelLimit = 50;

    private int $pollIteration = 0;

    private int $pollEpoch = 1;

    private string $checkInDate;

    private string $checkOutDate;

    private int $adults;

    private int $geoId;

    private string $selectedSortId = self::SORT_CHEAP_FIRST;

    private string $geoLocationStatus = 'unknown';

    public static function makeSourceParams(\App\UseCase\Search\Params $generalParams): self
    {
        $params = new self();
        $params->setCheckInDate($generalParams->getCheckInDate()->format('Y-m-d'));
        $params->setCheckOutDate($generalParams->getCheckOutDate()->format('Y-m-d'));
        $params->setAdults($generalParams->getAdults());
        $params->setGeoId(39); // @TODO сделать парсинг городов яндекса, пока оставим Ростов
        return $params;
    }
}
