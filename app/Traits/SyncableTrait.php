<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SyncableTrait
{
    /**
     * Order by sync status (unsynced first) and secondary column.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $secondaryColumn
     * @param  string  $secondaryDirection
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderBySync(Builder $query, string $secondaryColumn = 'id', string $secondaryDirection = 'desc'): Builder
    {
        return $query->orderByRaw('is_synced ASC')
            ->orderBy($secondaryColumn, $secondaryDirection);
    }

    /**
     * Filter by sync status based on is_synced boolean.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterSync(Builder $query, $status): Builder
    {
        return $query->when($status !== null && $status !== '', function ($q) use ($status) {
            $q->where('is_synced', (bool) $status);
        });
    }

    /**
     * Scope for unsynced only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnsynced(Builder $query): Builder
    {
        return $query->where('is_synced', false);
    }
    
    /**
     * Scope for synced only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSynced(Builder $query): Builder
    {
        return $query->where('is_synced', true);
    }
}
