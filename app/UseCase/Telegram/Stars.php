<?php

namespace App\UseCase\Telegram;

class Stars
{
    public const PREFIX_STARS = 'stars';
    private const STARS_ZERO_TEXT = 'без звёзд';
    private const BUTTONS_IN_ROW = 2;

    public function makeButtons(): array
    {
        $output = [];
        $row = [];
        for ($i = 1; $i < 7; ++$i) {
            $starsCount = $i - 1;

            $row[] = [
                'text' => $this->getStarsText($starsCount),
                'callback_data' => self::PREFIX_STARS . '-' . ($starsCount),
            ];

            if($i % self::BUTTONS_IN_ROW == 0) {
                $output[] = $row;
                $row = [];
            }
        }

        return $output;
    }

    public function parseStarsCount(string $callbackData): int
    {
        return (int)str_replace(self::PREFIX_STARS.'-', '', $callbackData);
    }

    private function getStarsText(int $starsCount): string
    {
        if ($starsCount == 0) {
            return self::STARS_ZERO_TEXT;
        }

        return str_repeat(Smiles::STAR, $starsCount);
    }
}
