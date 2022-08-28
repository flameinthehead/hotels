<?php

namespace App\UseCase\Sutochno;

use App\Models\Result;
use App\UseCase\Search\BookUrlEncoderInterface;
use App\UseCase\Search\SearchParamsFactoryInterface;
use App\UseCase\Search\SearchResultFactory;

class ResultFactory implements SearchResultFactory
{

    public static function makeResult(
        array $searchResult,
        SearchParamsFactoryInterface $params,
        BookUrlEncoderInterface $bookUrlEncoder
    ): ?Result {
        $facilities = [];
        foreach ($searchResult['conveniences'] as $facilityCode => $facilityValue) {
            if (!empty($facilityValue) && $facilityRuName = Facilities::getFacilityByCode($facilityCode)) {
                $facilities[] = $facilityRuName;
            }
        }

        list($checkInDate, $checkOutDate) = explode(';', $params->getOccupied());

        $result = new Result();
        $result->setName($searchResult['title']);
        $result->setAddress($searchResult['address']);
        $result->setBookLink($bookUrlEncoder->encode($searchResult['url']));
        $result->setCheckInDate(new \DateTime($checkInDate));
        $result->setCheckOutDate(new \DateTime($checkOutDate));
        $result->setDistanceToCenter(null);
        $result->setFacilities($facilities);
        $result->setHotelPreview(reset($searchResult['media']));
        $result->setLatitude($searchResult['lat']);
        $result->setLongitude($searchResult['lng']);
        $result->setPrice($searchResult['price']['full']);
        $result->setRef('sutochno');

        return $result;
    }
}
