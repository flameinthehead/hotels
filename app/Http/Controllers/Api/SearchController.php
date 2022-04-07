<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\HotelResource;
use App\UseCase\Search\Search;

class SearchController extends Controller
{
    public function __construct(Search $search)
    {
        $this->search = $search;
    }

    public function search(SearchRequest $request)
    {
        try {
            $results = $this->search->search($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['searchError' => [$e->getMessage()]],
            ], 403);
        }

        return HotelResource::collection($results);
    }
}
