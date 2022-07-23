<?php

namespace App\UseCase\Yandex;

use App\Models\Result;
use App\UseCase\Search\SearchParamsFactoryInterface;
use App\UseCase\Search\SearchResultFactory;

class ResultFactory implements SearchResultFactory
{
    public const BASE_RESULT_URL = 'https://travel.yandex.ru/hotels/';
    public const OPTIMAL_PREVIEW_SIZE = 'L';

    public static function makeResult(array $searchResult, SearchParamsFactoryInterface $params): ?Result
    {
        /* @var Params $params */
        $result = new Result();
        $result->setName($searchResult['hotel']['name']);
        $resultParams = [
            'adults' => $params->getAdults(),
            'checkinDate' => $params->getCheckInDate(),
            'checkoutDate' => $params->getCheckOutDate(),
        ];
        $result->setBookLink(
            self::BASE_RESULT_URL
            .$searchResult['hotel']['hotelSlug'].'?'
            .http_build_query($resultParams)
        );

        self::parseDistance($searchResult, $result);
        self::parsePreview($searchResult, $result);

        if (!empty($searchResult['hotel']['mainAmenities'])) {
            $result->setFacilities(array_column($searchResult['hotel']['mainAmenities'], 'name'));
        }

        $result->setPrice(reset($searchResult['offers'])['price']['value']);
        $result->setRef('yandex');
        $result->setLongitude($searchResult['hotel']['coordinates']['lon']);
        $result->setLatitude($searchResult['hotel']['coordinates']['lat']);
        $result->setCheckInDate(new \DateTime($params->getCheckinDate()));
        $result->setCheckOutDate(new \DateTime($params->getCheckoutDate()));
        $result->setAddress($searchResult['hotel']['address']);

        return $result;
    }

    private static function parseDistance(array $searchResult, Result $result): void
    {
        if(
            !empty($searchResult['hotel']['geoFeature']['name'])
            && $distance = preg_replace(
                '/(\d),(\d) км до центра/',
                '$1,$2',
                $searchResult['hotel']['geoFeature']['name'],
            )
        ) {
            $result->setDistanceToCenter((float)str_replace(',', '.', $distance));
        }
    }

    private static function parsePreview(array $searchResult, Result $result): void
    {
        $images = $searchResult['hotel']['images'];
        if (!empty($images)) {
            $firstImage = reset($images);
            $firstImage['sizes'] = array_filter($firstImage['sizes'], function ($size) {
                return $size['size'] === self::OPTIMAL_PREVIEW_SIZE;
            });
            $preview = sprintf($firstImage['urlTemplate'], reset($firstImage['sizes'])['size']);
            $result->setHotelPreview($preview);
        }
    }
}
