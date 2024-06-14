<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $types = [];
        foreach ($this->games as $game) {
            if ($game['dubbed'] ?? false == true)
                $types[] = 'dubbed';
            if ($game['modified'] ?? false == true)
                $types[] = 'modified';
            if ($game['subtitle'] ?? false == true)
                $types[] = 'subtitle';
            if ($game['iranian'] ?? false == true)
                $types[] = 'iranian';
        }
        $types = array_values(array_unique($types));

        $extra_searchables = [];
        foreach ($this->games as $game) {
            array_push($extra_searchables, ...$game['title_fa']);
            array_push($extra_searchables, ...$game['title_en']);
        }

        return [
            'featured_image' => $this->featured_image,
            'title_fa' => $this->title_fa ?? $this->games[0]['title_fa'][0] ?? '',
            'title_en' => $this->title_en ?? $this->games[0]['title_en'][0] ?? '',
            'release_dates' => $this->release_dates,
            'producers' => $this->producers->map(function ($company) {
                return [
                    'slug' => $company->slug,
                    'title_fa' => $company->title_fa[0],
                    'title_en' => $company->title_en[0],
                ];
            }),
            'publishers' => $this->publishers->map(function ($company) {
                return [
                    'slug' => $company->slug,
                    'title_fa' => $company->title_fa[0],
                    'title_en' => $company->title_en[0],
                ];
            }),
            'platforms' => $this->platforms,
            'types' => $types,
            'extra_searchables' => $extra_searchables,
            'slug' => $this->slug
        ];
    }
}
