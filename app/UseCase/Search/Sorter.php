<?php

namespace App\UseCase\Search;

use Illuminate\Support\Collection;

class Sorter
{
    public function sort(Collection $searchResults): array
    {
        $groupedBySimilar = $this->groupBySimilar($searchResults);
        return $this->formCheapestResults($groupedBySimilar);
    }

    private function groupBySimilar(Collection $searchResults): array
    {
        $output = [];

        /** @var Result $searchResult */
        foreach ($searchResults->toArray() as $searchResult) {
            $lat = round($searchResult->getLatitude(), 3);
            $lon = round($searchResult->getLongitude(), 3);

            $key = md5($lat.$lon);
            if (!isset($output[$key])) {
                $output[$key] = [];
            }

            $output[$key][] = $searchResult;
        }

        return $output;
    }

    private function formCheapestResults(array $groupedBySimilar)
    {
        $output = [];
        /** @var array $groupOfSimilar */
        foreach($groupedBySimilar as $groupOfSimilar) {
            if(count($groupedBySimilar) > 1) {
                usort($groupOfSimilar, function ($a, $b) {
                    return $a->getPrice() < $b->getPrice() ? -1 : 1;
                });
            }

            $output[] = reset($groupOfSimilar);
        }

        usort($output, function ($a, $b) {
            return $a->getPrice() < $b->getPrice() ? -1 : 1;
        });

        return $output;
    }
}
