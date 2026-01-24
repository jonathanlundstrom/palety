<?php

namespace App\Models;

use App\Enumerables\ImportCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model {
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contents';

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'label_en',
        'label_ua',
        'category',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'category' => ImportCategory::class,
    ];

    /**
     * Get the parcels associated with the content.
     * @return BelongsToMany
     */
    public function parcels(): BelongsToMany {
        return $this->belongsToMany(Parcel::class);
    }

    /**
     * Retrieves the correct label field based on the current application locale.
     * @return string
     */
    public static function label(): string {
        $locale = app()->getLocale();
        return $locale === 'ua' ? 'label_ua' : 'label_en';
    }
}
