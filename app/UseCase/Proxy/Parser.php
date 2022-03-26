<?php

namespace App\UseCase\Proxy;


use App\Models\Proxy;

class Parser
{
    public function update(SourceInterface $source)
    {
        $proxyList = $source->parse();

        foreach ($proxyList as $item) {
            $proxy = Proxy::firstOrCreate($item);
            $proxy->save();
        }
    }
}
