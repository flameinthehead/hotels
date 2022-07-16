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
                sprintf('%s Полная стоимость: *%s руб.*', Smiles::CREDIT_CARD, $oneResult->getPrice()),
                $oneResult->getFacilities()
                    ? sprintf('%s Удобства: *%s*', Smiles::TV, implode(', ', $oneResult->getFacilities()))
                    : '',
                sprintf(
                    '%s Расстояние до центра: *%s* км.',
                    Smiles::ROUND_PUSHPIN,
                    round($oneResult->getDistanceToCenter(), 2)
                ),
                sprintf(
                    '%s Ссылка для бронирования: *%s*',
                    Smiles::DOUBLE_RIGHT_ARROW,
                    $oneResult->getBookLink()
                ),
            ];

            $messages[] = implode("\r\n\r\n", $messageData);
        }

        return $messages;
    }
}
