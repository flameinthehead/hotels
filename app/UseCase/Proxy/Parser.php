<?php

namespace App\UseCase\Proxy;


use App\Models\Proxy;

class Parser
{
    public function update(SourceInterface $source): void
    {
        $proxyList = $source->parse();

        foreach ($proxyList as $item) {
            $proxy = Proxy::query()->where('address', $item['address'])->first();
            if (empty($proxy)) {
                $proxy = Proxy::firstOrCreate($item);
                $proxy->save();
            }
        }
    }
}
