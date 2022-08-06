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
            $query->select('address');
            /*$query->where(function ($query) use ($source) {
                $query->where($source, '1');
                $query->orWhereNull($source);
            });*/
        }

        if(!empty($proxySource)) {
            $query->where('source', $proxySource);
        }

        $query->orderBy('updated_at', 'DESC');

        return $query->get()->pluck('address')->all();
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
