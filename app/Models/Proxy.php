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

    public function forChecker(string $source = '', string $proxySource = null): array
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

//        $query->where('address', '209.166.175.201:8080'); // @TODO убрать

        $query->orderBy('updated_at', 'DESC');

        return $query->get()->all();
    }

    public function getRandBySource(string $source): self
    {
        $query = self::query()->where($source, '1')->inRandomOrder();
        return $query->firstOrFail();
    }

    public function getAllEnabledBySource(string $source, int $limit = 10): array
    {
        return self::query()->where($source, '1')->inRandomOrder()->limit($limit)->get()->all();
    }
}
