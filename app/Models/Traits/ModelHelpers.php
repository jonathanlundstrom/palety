<?php namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ModelHelpers {

    /**
     * Retrieves the correct label field based on the current application locale.
     * @return string
     */
    public static function label(): string {
        $locale = app()->getLocale();
        return $locale === 'ua' ? 'label_ua' : 'label_en';
    }

    /**
     * Apply a query scope to list records with specified columns and order.
     *
     * @param Builder $query The query builder instance.
     * @param array $columns The columns to select in the query.
     * @param string $order_by The column to order the results by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeList(Builder $query, array $columns, string $order_by): Builder {
        return $query->select(...$columns)
            ->orderBy($order_by);
    }

}
