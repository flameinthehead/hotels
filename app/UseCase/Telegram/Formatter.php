<?php

namespace App\UseCase\Telegram;

use App\Models\Result;
use Illuminate\Support\Facades\Log;

class Formatter
{
    public const NO_PHOTO = 'no_photo_';

    /** @var Result[] $searchResults */
    public function formatSearchResults(array $searchResults): array
    {
        $messages = [];
        foreach ($searchResults as $oneResult) {
            $messageData = [
                $oneResult->getStars() ? str_repeat(Smiles::STAR, $oneResult->getStars()) : null,
                sprintf('%s Название: <b>%s</b>', Smiles::HOTEL, $oneResult->getName()),
                $this->getDates($oneResult),
                sprintf('%s Адрес: <b><a href="https://yandex.ru/maps/?text=%s">%s</a></b>',
                    Smiles::ROUND_PUSHPIN,
                    $oneResult->getAddress(),
                    $oneResult->getAddress()
                ),
                sprintf('%s Полная стоимость: <b>%s руб.</b>', Smiles::CREDIT_CARD, $oneResult->getPrice()),
                $this->getFacilities($oneResult),
                $this->getDistanceToCenter($oneResult),
                $this->getBookLink($oneResult),
            ];

            $photo = (
                !empty($oneResult->getHotelPreview())
                    ? $oneResult->getHotelPreview()
                    : self::NO_PHOTO . $oneResult->getName()
            );

            $messages[$photo] = implode("\r\n\r\n", array_filter($messageData));
        }

        return $messages;
    }

    private function getDates(Result $oneResult): string
    {
        return sprintf(
            '%s Даты: <b>с %s по %s</b>',
            Smiles::CALENDAR,
            $oneResult->getCheckInDate()->format('d.m.Y'),
            $oneResult->getCheckOutDate()->format('d.m.Y')
        );
    }

    private function getFacilities(Result $oneResult): string
    {
        return $oneResult->getFacilities()
            ? sprintf('%s Удобства: <b>%s</b>', Smiles::TV, $oneResult->getFacilities())
            : '';
    }

    private function getDistanceToCenter(Result $oneResult): string
    {
        return sprintf(
            '%s Расстояние до центра: <b>%s</b> км.',
            Smiles::RULER,
            round($oneResult->getDistanceToCenter(), 2)
        );
    }

    private function getBookLink(Result $oneResult): string
    {
        return sprintf(
            '%s Ссылка для бронирования: <b>%s</b>',
            Smiles::DOUBLE_RIGHT_ARROW,
            $oneResult->getBookLink()
        );
    }
}
