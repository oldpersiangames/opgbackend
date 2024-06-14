<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\TGFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Item::latest()->get(['id', 'slug']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->except(['producers', 'publishers', 'contributes', 'photos']);


        $item = Item::create($input);
        $item->producers()->attach($request->producers, ['relation' => 'producer']);
        $item->publishers()->attach($request->publishers, ['relation' => 'publisher']);

        $item->contributes()->createMany($request->contributes);


        foreach ($request->photos as $photo) {
            if (isset($photo["dataURL"]))
                $item->addMediaFromBase64($photo["dataURL"])
                    ->usingFileName(str_replace('/tmp/', '', tempnam(sys_get_temp_dir(), 'media-library')) . '.jpg')
                    ->toMediaCollection();
        }

        return $item;
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Item::with(['producers:id', 'publishers:id', 'contributes'])->findOrFail($id);

        $item->producers = $item->producers->map->id->values();
        $item->unsetRelation('producers');

        $item->publishers = $item->publishers->map->id->values();
        $item->unsetRelation('publishers');


        $photos = $item->getMedia()
            ->map(function ($photo) {
                return ["id" => $photo->id, "url" => $photo->getUrl()];
            });

        return [...$item->toArray(), 'photos' => $photos];
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {

        $input = $request->except(['producers', 'publishers', 'contributes', 'photos']);


        if ($request->filled('photos')) {
            // Photos to keep in media library
            $toKeep = collect(array_filter($request->photos, function ($photo) {
                return !isset($photo["dataURL"]);
            }));
            $toKeepIds = array_column($toKeep->toArray(), 'id');

            // Remove other items from media library
            foreach ($item->getMedia() as $media) {
                if (!in_array($media->id, $toKeepIds))
                    $media->delete();
            }

            // Upload photos
            foreach ($request->photos as $photo) {
                if (isset($photo["dataURL"]))
                    $item->addMediaFromBase64($photo["dataURL"])->usingFileName(str_replace('/tmp/', '', tempnam(sys_get_temp_dir(), 'media-library')) . '.jpg')->toMediaCollection();
            }
        }

        $item->update($input);
        $item->producers()->syncWithPivotValues($request->producers, ['relation' => 'producer']);
        $item->publishers()->syncWithPivotValues($request->publishers, ['relation' => 'publisher']);

        $currentContributes = $item->contributes->toArray();
        $newContributes = $request->contributes;

        $contributesToAdd = array_values(array_filter($newContributes, function ($value) {
            return $value["new"] ?? false;
        }));
        $contributesToEdit = array_values(array_filter($newContributes, function ($value) {
            return !($value["new"] ?? false);
        }));
        $contributesToDelete = array_values(array_diff(array_column($currentContributes, 'id'), array_column($contributesToEdit, 'id')));

        foreach ($contributesToEdit as $value) {
            $item->contributes->firstWhere('id', $value['id'])->update($value);
        }

        $item->contributes()->whereIn('id', $contributesToDelete)->delete();

        $item->contributes()->createMany($contributesToAdd);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();
    }
}
