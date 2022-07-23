<?php

namespace App\Http\Resources;

use App\Models\Result;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /* @var Result $this */
        return [
            'name' => $this->getName(),
            'price' => $this->getPrice(),
            'bookLink' => $this->getBookLink(),
            'facilities' => $this->getFacilities(),
            'distanceToCenter' => $this->getDistanceToCenter(),
            'hotelPreview' => $this->getHotelPreview(),
            'ref' => $this->getRef()
        ];
    }
}
