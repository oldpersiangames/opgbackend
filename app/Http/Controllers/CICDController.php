<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use App\Models\Game;
use App\Models\Item;
use App\Models\TGFile;
use SergiX44\Nutgram\Nutgram;

class CICDController extends Controller
{
    public function makeBackup(Request $request)
    {
    

        $backupPath = storage_path('app/opg-backups/opgbackend.sql');

        Process::run(
    'PGPASSWORD="' . config('database.pgsql.password') . '" pg_dump ' .
    '-h ' . config('database.pgsql.host') . ' ' .
    '-U ' . config('database.pgsql.username') . ' ' .
    config('database.pgsql.database') . ' > ' . $backupPath
        );

        // Process::path('/opgactions/opg-backups')->run('mysqldump --skip-extended-insert --skip-dump-date -h' . env('DB_HOST') . ' -u' . env('DB_USERNAME') . ' -p' . env('DB_PASSWORD') . ' ' . env('DB_DATABASE') . ' > opgbackend.sql');

        // Process::path('/opgactions/opg-backups')->run('git add .');
        // Process::path('/opgactions/opg-backups')->run('git commit -m "' . Carbon::now()->setTimezone('UTC')->toDateTimeString() . '"');
        // Process::path('/opgactions/opg-backups')->run('git push');
    }

    public function beforeIa(Request $request)
    {
        if (hash('sha256', $request->key) != env('OPG_KEY_HASH'))
            abort(403);

        $games = Game::where('status', 'published')->whereNull('ia_id')->where('selling', false)
            ->whereNot('slug', 'grand-theft-auto-san-andreas-oldpersiangames')
            ->get(['slug', 'tgfiles', 'games', 'title_en'])->map(function ($game) {
                $tgfiles = TGFile::whereIn('file_unique_id', $game->tgfiles)->orderByRaw("FIELD(file_unique_id, '" . implode("','", $game->tgfiles) . "')")->get(['file_name', 'file_size', 'file_id', 'file_unique_id', 'date']);
                $game->tgfiles = $tgfiles->toArray();

                $game->title = $game->title_en ?? $game->games[0]['title_en'][0];
                $game->type = 'game';
                unset($game->title_en);
                unset($game->games);
                return $game;
            });


        $items = Item::whereNull('ia_id')->where('selling', false)
            ->get(['slug', 'tgfiles', 'title_en'])->map(function ($item) {
                $tgfiles = TGFile::whereIn('file_unique_id', $item->tgfiles)->orderByRaw("FIELD(file_unique_id, '" . implode("','", $item->tgfiles) . "')")->get(['file_name', 'file_size', 'file_id', 'file_unique_id', 'date']);
                $item->tgfiles = $tgfiles->toArray();

                $item->title = $item->title_en;
                $item->type = 'item';
                return $item;
            });

        return [...$games, ...$items];
    }

    public function setIa(Request $request, Nutgram $bot)
    {
        if (hash('sha256', $request->key) != env('OPG_KEY_HASH'))
            abort(403);

        $value = ['ia_id' => $request->slug];
        if ($request->has('filesJSON'))
            $value['files'] = $request->filesJSON;

        $item = Game::where('status', 'published')->where('slug', $request->slug)->whereNull('ia_id')->first();
        if (!$item) $item = Item::where('slug', $request->slug)->whereNull('ia_id')->first();

        $item->update($value);

        $bot->sendMessage(
            text: $request->slug,
            chat_id: env("OWNER_TG_ID")
        );
    }
}
