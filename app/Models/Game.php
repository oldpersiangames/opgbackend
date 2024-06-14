<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Game extends Model  implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];
    protected $appends = ['featured_image'];
    protected $hidden = ['media'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    protected function casts(): array
    {
        return [
            'games' => 'array',
            'photos' => 'array',
            'release_dates' => 'array',
            'prices' => 'array',
            'defects_fa' => 'array',
            'defects_en' => 'array',
            'tgfiles' => 'array',
            'files' => 'array',
            'links' => 'array',
            'released_on_en' => 'array',
            'released_on_fa' => 'array',
            'platforms' => 'array',
            'selling' => 'boolean',
        ];
    }

    public function producers(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->wherePivot('relation', 'producer');
    }

    public function publishers(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->wherePivot('relation', 'publisher');
    }

    public function contributes(): MorphMany
    {
        return $this->morphMany(Contribute::class, 'contributable');
    }

    public function getFeaturedImageAttribute(): string
    {
        return $this->getFirstMediaUrl();
    }
}
