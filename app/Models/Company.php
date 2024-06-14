<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['pivot'];

    protected function casts(): array
    {
        return [
            'title_en' => 'array',
            'title_fa' => 'array'
        ];
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
    }
}
