<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Item;
use App\Models\TGFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PublicApiController extends Controller
{
    public function games()
    {
        return GameResource::collection(Game::with(['producers:slug,title_fa,title_en', 'publishers:slug,title_fa,title_en'])->latest()->get());
    }

    public function game($slug)
    {
        $game = Game::with(['producers:id,title_en,title_fa', 'publishers:id,title_en,title_fa', 'contributes:id,user_id,time,contributable_type,contributable_id,contribute,sort',  'contributes.user:id,name'])->where('slug', $slug)->firstOrFail();
        $tgfiles = TGFile::whereIn('file_unique_id', $game->tgfiles)->orderByRaw("FIELD(file_unique_id, '" . implode("','", $game->tgfiles) . "')")->get(['file_id', 'file_name', 'file_size', 'date']);
        $game->tgfiles = $tgfiles->toArray();

        $titles = [];
        if ($game->title_en)
            $titles[] = $game->title_en;
        foreach ($game->games as $g) {
            array_push($titles, ...$g['title_en']);
        }

        $relatedGames = Game::with(['producers:id,title_en,title_fa', 'publishers:id,title_en,title_fa'])
            ->whereNot('id', $game->id)
            ->where(function (Builder $query) use ($titles) {
                $query->whereIn('title_en', $titles);
                foreach ($titles as $title) {
                    $query->orWhereJsonContains('games', ['title_en' => $title]);
                }
            })->get(['id', 'slug', 'title_fa', 'title_en', 'games']);

        $game->related = $relatedGames;

        $photos = $game->getMedia()->map(function ($media) {
            return $media->getFullUrl();
        });

        return [...$game->toArray(), 'photos' => $photos];
    }

    public function items()
    {
        return Item::latest()->with(['producers:title_fa,title_en', 'publishers:title_fa,title_en'])->get(['id', 'slug', 'title_fa', 'title_en', 'release_dates']);
    }

    public function item($slug)
    {
        $item = Item::with(['producers:slug,title_en,title_fa', 'publishers:slug,title_en,title_fa', 'contributes:user_id,contributable_type,contributable_id,contribute',  'contributes.user:id,name'])->where('slug', $slug)->firstOrFail();
        $tgfiles = TGFile::whereIn('file_unique_id', $item->tgfiles)->orderByRaw("FIELD(file_unique_id, '" . implode("','", $item->tgfiles) . "')")->get(['file_id', 'file_name', 'file_size', 'date']);
        $item->tgfiles = $tgfiles->toArray();

        $photos = $item->getMedia()->map(function ($media) {
            return $media->getFullUrl();
        });
        return [...$item->toArray(), 'photos' => $photos];
    }

    public function users()
    {
        return User::orderBy('name', 'asc')->with(['contributes:id,user_id,contributable_type,contributable_id,contribute'])->get(['id', 'name', 'telegram']);
    }

    public function lostGames()
    {
        return str_replace("lost-games/", "", Storage::disk('public')->files('lost-games'));
    }

    public function nofuzy1()
    {
        header("Content-type: text/csv");
        $games = Game::with(['producers:slug,title_fa,title_en', 'publishers:slug,title_fa,title_en'])->latest()->get();
        echo "id,tilte_en,title_fa,publisher\n";
        $games->each(function ($game) {
            echo $game->id . "," . ($game->title_en ?? $game->games[0]['title_en'][0] ?? '') . "," . ($game->title_fa ?? $game->games[0]['title_fa'][0] ?? '') . "," . ($game->publishers->first()?->title_fa[0] ?? '') . "\n";
        });
    }

    public function nofuzy2()
    {
        $games = Game::with(['producers:title_fa', 'publishers:title_fa'])->latest()->get()->map(function ($game) {

            if ($game->tgfiles)
                $tgfiles = TGFile::whereIn('file_unique_id', $game->tgfiles)->orderByRaw("FIELD(file_unique_id, '" . implode("','", $game->tgfiles) . "')")->get(['file_id', 'file_name', 'file_size', 'date']);
            else $tgfiles = collect([]);

            return [
                'slug' => $game->slug,
                'title_en' => $game->title_en ?? $game->games[0]['title_en'][0] ?? '',
                'title_fa' => $game->title_fa ?? $game->games[0]['title_fa'][0] ?? '',
                'producers' => $game->publishers,
                'publisher' => $game->publishers,
                'size' => $tgfiles->sum('file_size'),
                'size_mb' => $tgfiles->sum('file_size') / 1024 / 1024,
            ];
        });
        return $games;
    }
}
