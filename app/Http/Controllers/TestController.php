<?php

namespace App\Http\Controllers;

use App\UseCase\Yandex\Search;

class TestController extends Controller
{
    private $yandexSearch;

    public function __construct(Search $yandexSearch)
    {
        $this->yandexSearch = $yandexSearch;
    }

    public function yandex()
    {
        /*$params = [
            'startSearchReason' => 'sort',
            'pageHotelCount' => 25,
            'pricedHotelLimit' => 26,
            'totalHotelLimit' => 50,
            'pollIteration' => 0,
            'pollEpoch' => 0,
            'geoId' => 39,
            'adults' => 2,
            'checkinDate' => '2022-04-01',
            'checkoutDate' => '2022-04-02',
            'geoLocationStatus' => 'unknown',
            'selectedSortId' => 'cheap-first',
        ];*/
        $params = [
            'startSearchReason' => 'sort',
            'pageHotelCount' =>  25,
            'pricedHotelLimit' => 26,
            'totalHotelLimit' => 50,
            'pollIteration' => 0,
            'pollEpoch' => 1,
            'checkinDate' => '2022-03-21',
            'checkoutDate' => '2022-03-22',
            'adults' => 2,
            'geoId' => 39,
            'selectedSortId' => 'cheap-first',
            'geoLocationStatus' => 'unknown',
        ];


        $result = $this->yandexSearch->search($params);
        dump($result[0]);
        dump($result[1]);
        dump($result[2]);
        dump($result[3]);
        dump($result[4]);
        dd($result);
    }
}
