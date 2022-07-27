<?php

namespace App\UseCase\Ostrovok;

use App\Models\Result;
use App\UseCase\Search\SearchParamsFactoryInterface;
use App\UseCase\Search\SearchResultFactory;
use Illuminate\Support\Facades\Log;

class ResultFactory implements SearchResultFactory
{
    public const BASE_RESULT_URL = 'https://ostrovok.ru/hotel';
    public const PREVIEW_WIDTH = '640';
    public const PREVIEW_HEIGHT = '400';

    public static function makeResult(array $searchResult, SearchParamsFactoryInterface $params): ?Result
    {
        $hotelName = $searchResult['static_vm']['name'] ?? null;
        $price = $searchResult['rates'][0]['payment_options']['payment_types'][0]['amount'] ?? null;


        $checkInDate = new \DateTime($params->getArrivalDate());
        $checkOutDate = new \DateTime($params->getDepartureDate());

        /** @var \App\UseCase\Ostrovok\Params $params */
        $regionCatalogSlug = $searchResult['static_vm']['region_catalog_slug'];
        $bookLink = sprintf(
            self::BASE_RESULT_URL . '/%s/mid%s/%s/?dates=%s-%s&guests=%s',
            $regionCatalogSlug,
            $searchResult['master_id'],
            $searchResult['ota_hotel_id'],
            $checkInDate->format('d.m.Y'),
            $checkOutDate->format('d.m.Y'),
            $params->getAdults(),
        );

        $facilities = [];
        foreach ($searchResult['static_vm']['serp_filters'] as $facilityCode) {
            if ($facilityTitle = Facilities::getFacilityByCode($facilityCode)) {
                $facilities[] = $facilityTitle;
            }
        }

        $distanceToCenter = isset($searchResult['static_vm']['search_region_center_distance']) ?
            (float)$searchResult['static_vm']['search_region_center_distance'] : null;

        $preview = null;
        if (isset($searchResult['static_vm']['images'][0]['tmpl'])) {
            $preview = str_replace(
                '{size}',
                sprintf('%sx%s', self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT),
                $searchResult['static_vm']['images'][0]['tmpl']
            );
        }

        $result = new Result();
        $result->setName($hotelName);
        $result->setPrice($price);
        $result->setBookLink($bookLink);
        $result->setFacilities($facilities);
        $result->setDistanceToCenter($distanceToCenter);
        $result->setHotelPreview($preview);
        $result->setRef('ostrovok');
        $result->setLatitude($searchResult['static_vm']['latitude']);
        $result->setLongitude($searchResult['static_vm']['longitude']);
        $result->setCheckInDate($checkInDate);
        $result->setCheckOutDate($checkOutDate);
        $result->setAddress($searchResult['static_vm']['address']);
        $result->setStars(($searchResult['static_vm']['star_raiting']) / 10);

        return $result;
    }
}
