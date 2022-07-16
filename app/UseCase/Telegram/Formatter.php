<?php

namespace App\UseCase\Telegram;

use App\UseCase\Search\Result;

class Formatter
{
    /** @var Result[] $searchResults */
    public function formatSearchResults(array $searchResults): array
    {
        $messages = [];
        foreach ($searchResults as $oneResult) {
            $messageData = [
                sprintf('%s Название: *%s*', Smiles::HOTEL, $oneResult->getName()),
                $this->getDates($oneResult),
                sprintf('%s Адрес: *%s*', Smiles::ROUND_PUSHPIN, $oneResult->getAddress()),
                sprintf('%s Полная стоимость: *%s руб.*', Smiles::CREDIT_CARD, $oneResult->getPrice()),
                $this->getFacilities($oneResult),
                $this->getDistanceToCenter($oneResult),
                $this->getBookLink($oneResult),
            ];

            $messages[$oneResult->getHotelPreview()] = implode("\r\n\r\n", $messageData);
        }

        return $messages;
    }

    private function getDates(Result $oneResult): string
    {
        return sprintf(
            '%s Даты: с %s по %s',
            Smiles::CALENDAR,
            $oneResult->getCheckInDate()->format('d.m.Y'),
            $oneResult->getCheckOutDate()->format('d.m.Y')
        );
    }

    private function getFacilities(Result $oneResult): string
    {
        return $oneResult->getFacilities()
            ? sprintf('%s Удобства: *%s*', Smiles::TV, implode(', ', $oneResult->getFacilities()))
            : '';
    }

    private function getDistanceToCenter(Result $oneResult): string
    {
        return sprintf(
            '%s Расстояние до центра: *%s* км.',
            Smiles::RULER,
            round($oneResult->getDistanceToCenter(), 2)
        );
    }

    private function getBookLink(Result $oneResult): string
    {
        return sprintf(
            '%s Ссылка для бронирования: *%s*',
            Smiles::DOUBLE_RIGHT_ARROW,
            $oneResult->getBookLink()
        );
    }
}
