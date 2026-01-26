<?php namespace App\Models\Traits;

trait ModelHelpers {

    /**
     * Retrieves the correct label field based on the current application locale.
     * @return string
     */
    public static function label(): string {
        $locale = app()->getLocale();
        return $locale === 'ua' ? 'label_ua' : 'label_en';
    }

}
