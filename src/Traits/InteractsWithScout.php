<?php

namespace Kainiklas\FilamentScout\Traits;

use Illuminate\Database\Eloquent\Builder;

trait InteractsWithScout
{
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTableQuery($query);

        $search = $this->getTableSearch();

        if (blank($search)) {
            return $query;
        }

        $keys = $this->getModel()::search($search)->keys();

        return $query->whereIn('id', $keys);
    }
    
    protected function scoutQuerySorting(string $indexColumn, Builder $query, string $direction): Builder
    {
        $indexedRecords = $this->getModel()::search()
            ->whereIn('id', $query->pluck('id'))
            ->sortBy($indexColumn, $direction)
            ->withTrashed()
            ->get()
            ->pluck('id')
            ->toArray();
        
        if (empty($indexedRecords)) {
            return $query;
        }
        
        $rawQueryString = 'CASE ';
        foreach ($indexedRecords as $orderIndex => $indexedRecord) {
            $orderRawQuery .= 'WHEN id = ' . $indexedRecord['id'] . ' THEN ' . ($orderIndex + 1) . ' ';
        }
        $orderRawQuery .= 'END ' . $direction;

        return $query->orderByRaw($orderRawQuery);
    }
}
