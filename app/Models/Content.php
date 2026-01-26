<?php

namespace App\Models;

use App\Enumerables\ImportCategory;
use App\Models\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model {
    use ModelHelpers, HasFactory, SoftDeletes;

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
}
