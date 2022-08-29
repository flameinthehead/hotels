<?php

namespace App\UseCase\Proxy;


use App\Models\Proxy;

class Parser
{
    public function update(SourceInterface $source, Proxy $proxy = null)
    {
        $proxyList = $source->parse($proxy);

        foreach ($proxyList as $item) {
            $proxy = Proxy::query()->where('address', $item['address'])->first();
            if (empty($proxy)) {
                $proxy = Proxy::firstOrCreate($item);
                $proxy->save();
            }
        }
    }
}
