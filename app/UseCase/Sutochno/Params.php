<?php

namespace App\UseCase\Sutochno;

use App\UseCase\Search\SearchParamsFactoryInterface;

class Params implements SearchParamsFactoryInterface
{
    private int $maxGuests;

    private string $occupied;

    private int $currencyId = 1;

    private int $zoom = 13;

    private int $count = 50;

    private int $offset = 0;

    private array $NE = [];

    private array $SW = [];

    public static function makeSourceParams(\App\UseCase\Search\Params $generalParams): self
    {
        $params = new self();
        $params->setOccupied(
            implode(
                ';',
                [
                    $generalParams->getCheckInDate()->format('Y-m-d'),
                    $generalParams->getCheckOutDate()->format('Y-m-d')
                ]
            )
        );
        $params->setMaxGuests($generalParams->getAdults());
        $cityData = json_decode($generalParams->getCity()->sutochnoCity()->first()->sutochno_city_data, true);
        $params->setNE($cityData['NE']);
        $params->setSW($cityData['SW']);

        return $params;
    }

    /**
     * @return int
     */
    public function getMaxGuests(): int
    {
        return $this->maxGuests;
    }

    /**
     * @param int $maxGuests
     */
    public function setMaxGuests(int $maxGuests): void
    {
        $this->maxGuests = $maxGuests;
    }

    /**
     * @return string
     */
    public function getOccupied(): string
    {
        return $this->occupied;
    }

    /**
     * @param string $occupied
     */
    public function setOccupied(string $occupied): void
    {
        $this->occupied = $occupied;
    }

    /**
     * @return string
     */
    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    /**
     * @param string $currencyId
     */
    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    /**
     * @return int
     */
    public function getZoom(): int
    {
        return $this->zoom;
    }

    /**
     * @param int $zoom
     */
    public function setZoom(int $zoom): void
    {
        $this->zoom = $zoom;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return array
     */
    public function getNE(): array
    {
        return $this->NE;
    }

    /**
     * @param array $NE
     */
    public function setNE(array $NE): void
    {
        $this->NE = $NE;
    }

    /**
     * @return array
     */
    public function getSW(): array
    {
        return $this->SW;
    }

    /**
     * @param array $SW
     */
    public function setSW(array $SW): void
    {
        $this->SW = $SW;
    }
}
