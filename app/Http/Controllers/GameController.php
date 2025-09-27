<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\TGFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class GameController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Game::latest()->get(['id', 'slug', 'title_fa', 'title_en', 'games'])->transform(function ($game) {
            return [
                'id' => $game->id,
                'slug' => $game->slug,
                'status'=>$game->status,
                'title_fa' => $game->title_fa ?? $game->games[0]['title_fa'][0] ?? '',
                'title_en' => $game->title_en ?? $game->games[0]['title_en'][0] ?? '',
            ];
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $input = $request->except(['producers', 'publishers', 'contributes', 'photos']);

            $game = Game::create($input);
            $game->producers()->attach($request->producers, ['relation' => 'producer']);
            $game->publishers()->attach($request->publishers, ['relation' => 'publisher']);
            $game->contributes()->createMany($request->contributes);


            foreach ($request->photos as $photo) {
                if (isset($photo["dataURL"]))
                    $game->addMediaFromBase64($photo["dataURL"])
                        ->usingFileName(str_replace('/tmp/', '', tempnam(sys_get_temp_dir(), 'media-library')) . '.jpg')
                        ->toMediaCollection();
            }


            return $game;
        });
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $game = Game::with(['producers:id', 'publishers:id', 'contributes'])->findOrFail($id);


        return [
            ...$game->toArray(),
            'producers' => $game->producers->pluck('id'),
            'publishers' => $game->publishers->pluck('id'),
            'photos' => $game->getMedia()
                ->map(function ($photo) {
                    return ["id" => $photo->id, "url" => $photo->getUrl()];
                }),
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        return DB::transaction(function () use ($game, $request) {
            if ($request->filled('photos')) {
                $photos = collect($request->photos);

                // Photos to keep in the media library
                $toKeepIds = $photos->whereNotNull('id')->pluck('id');

                // Remove other items from the media library
                foreach ($game->getMedia() as $media) {
                    if ($toKeepIds->doesntContain($media->id))
                        $media->delete();
                }

                // Upload photos
                $photos->whereNotNull('dataURL')->each(function ($photo) use ($game) {
                    $game->addMediaFromBase64($photo["dataURL"])->usingFileName(str_replace('/tmp/', '', tempnam(sys_get_temp_dir(), 'media-library')) . '.jpg')->toMediaCollection();
                });
            }

            $game->update($request->except(['producers', 'publishers', 'contributes', 'photos']));
            $game->producers()->syncWithPivotValues($request->producers, ['relation' => 'producer']);
            $game->publishers()->syncWithPivotValues($request->publishers, ['relation' => 'publisher']);

            $game->contributes()->delete();
            $game->contributes()->createMany($request->contributes);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        $game->delete();
    }
}
