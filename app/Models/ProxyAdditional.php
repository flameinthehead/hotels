<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxyAdditional extends Model
{
    protected $table = 'proxies_additional';

    public function proxy()
    {
        return $this->belongsTo(Proxy::class);
    }
}
