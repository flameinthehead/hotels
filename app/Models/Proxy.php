<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Proxy extends Model
{
    protected $fillable = [
        'address',
        'source',
        'enabled',
    ];

    public function forChecker(string $source = '', string $proxySource = null)
    {
        $query = self::query();
        if (!empty($source) && Schema::hasColumn($this->getTable(), $source)) {
            $query->where(function ($query) use ($source) {
                /* @var Builder $query */
                $query->where($source, '1');
                $query->orWhereNull($source);
            });
        }

        if(!empty($proxySource)) {
            $query->where('source', $proxySource);
        }

        return $query->get()->all();
    }

    public function getRandBySource(string $source): self
    {
        return self::query()->where($source, '1')->inRandomOrder()->first();
    }
}
