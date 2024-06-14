<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Contribute;
use App\Models\Game;
use App\Models\TGFile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/* It was a temporary script used to manage transfering data from the legacy website to the new one.
This command not used anymore */

class TransferContents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transfer-contents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ctext = [
            'game' => 'تهیه بازی',
            'cover' => 'تهیه تصویر',
            'details' => 'نگارش مشخصات',
            'manual' => 'تهیه کتابچه',
            'engineering' => 'مهندسی معکوس'
        ];


        // $result = Process::run("node -e \"const myToken = require('/archive/tmp/oldpersiangames.github.io/src/_data/games.js');
        $result = Process::run("node -e \"const myToken = require('/oldpersiangames.github.io/src/_data/games.js');
        myToken().then(token => {
          process.stdout.write(JSON.stringify(token));
        });\"");
        // echo $result->errorOutput();
        $games = json_decode($result->output(), true);


        $tgfiles = File::json('/sourceout-20230918.json');
        $tgfiles = array_reverse($tgfiles);

        foreach ($games as $index => $game) {

            if (isset($game['tgfiles']) && $game['tgfiles']) {
                if ((!isset($game['created_at']))) {
                    foreach ($tgfiles as $tgfile) {
                        if (isset($tgfile['document']) && $tgfile['document']['file_unique_id'] == $game['tgfiles'][0]['file_unique_id']) {
                            $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $tgfile['date']);
                            $datetime->shiftTimezone('Asia/Tehran');
                            $datetime->setTimezone('UTC');
                            $games[$index]['created_at'] = $datetime->toAtomString();
                            // $games[$index]['created_at'] = $tgfile['date'];
                        }
                    }
                }
            } else {
                unset($games[$index]);
            }
        }

        $key_values = array_column($games, 'created_at');
        array_multisort($key_values, SORT_ASC, $games);



        // $companies = File::json('/archive/tmp/oldpersiangames.github.io/src/_data/companies.json');
        $companies = File::json('/oldpersiangames.github.io/src/_data/companies.json');
        foreach ($companies as $key => $company) {
            Company::create(['title_en' => [$company["title"]], 'title_fa' => [$company["titlefa"]], 'slug' => $key]);
        }

        foreach ($games as $game) {
            $toAssign = [];
            $toAssign['games'] = [];
            foreach ($game['games'] as $subgame) {
                $toAssign['games'][] = [
                    "title_fa" => $subgame['titlefa'] ? $subgame['titlefa'] : $game['titlefa'],
                    "title_en" => $subgame['title'] ? $subgame['title'] : $game['title'],
                    "dubbed" => $subgame['dubbed'],
                    "modified" => $subgame['modified'] ?? false,
                    "subtitle" => $subgame['subtitle'] ?? false,
                    "iranian" => $subgame['iranian'],
                ];
            }

            if ($game['created_at'] !== '0') {
                $toAssign['created_at'] = $game['created_at'];
                $toAssign['updated_at'] = $game['created_at'];
            }

            if (count($game['games']) > 1) {
                $toAssign['collection_title_fa'] = $game['titlefa'][0];
                $toAssign['collection_title_en'] = $game['title'][0];
            }

            if (isset($game['release_date'])) {
                $toAssign['release_dates'] = [];
                foreach ($game['release_date'] as $date) {
                    $toAssign['release_dates'][] = $date['date'];
                }
            }

            $toAssign['prices'] = [];
            foreach ($game['price'] as $price) {
                $toAssign['prices'][] = $price;
            }

            $toAssign['platforms'] = [];
            foreach ($game['platform'] as $platform) {
                $toAssign['platforms'][] = $platform;
            }

            if (isset($game['ia_id']) && $game['ia_id']) {
                $toAssign['ia_id'] = $game['ia_id'];
            }

            if (isset($game['released_on'])) {
                $toAssign['released_on_en'] = [];
                $toAssign['released_on_fa'] = [];
                foreach ($game['released_on'] as $index => $released_on) {
                    if ($released_on == 'web') {
                        $toAssign['released_on_en'][] = "Web";
                        $toAssign['released_on_fa'][] = "وب";
                    } else {
                        $toAssign['released_on_en'][] = $game['no_of_discs'][$index] . " " . $released_on;
                        $toAssign['released_on_fa'][] = $game['no_of_discs'][$index] . " " . str_ireplace('DVD', 'دی وی دی', str_ireplace('CD', 'سی دی', $released_on));
                    }
                }
            }

            $toAssign['defects_fa'] = [];
            $toAssign['defects_en'] = [];
            foreach ($game['defects'] as $defect) {
                $toAssign['defects_fa'][] = $defect;
                $toAssign['defects_en'][] = $defect;
            }

            $toAssign['slug'] = $game['id'];
            $toAssign['selling'] = $game['selling'];

            if (isset($game['description'])) {
                $toAssign['notes_fa'] = $game['description'];
                $toAssign['notes_en'] = $game['description'];
            }

            if (isset($game['tgfiles']))
                $toAssign['tgfiles'] = array_column($game['tgfiles'], 'file_unique_id');
            if (isset($game['files']))
                $toAssign['files'] = $game['files'];

            if (isset($game['links'])) {
                $toAssign['links'] = [];
                foreach ($game['links'] as $index => $link) {
                    $toAssign['links'][] = [
                        "id" => $index + 1,
                        "title_fa" => $link['title'],
                        "title_en" => $link['title'],
                        "url" => $link['url']
                    ];
                }
            }

            $g = Game::create($toAssign);


            if ($game['contributors']) {
                foreach ($game['contributors'] as $contribute) {
                    $user = User::firstOrCreate([
                        'name' => $contribute['username'],
                        'email' => $contribute['username'] . "@oldpersiangames.org",

                    ]);

                    foreach ($contribute['contribute'] as $cc) {
                        $g->contributes()->create([
                            "user_id" => $user->id,
                            "contribute" => $ctext[$cc]
                        ]);
                    }
                }
            }

            if ($game['photos']) {
                // if (!file_exists('/archive/tmp/opgbackend/storage/app/game-photos/' . $g->id . '/'))
                // mkdir('/archive/tmp/opgbackend/storage/app/game-photos/' . $g->id . '/', 0777, true);
                if (!file_exists('/usr/share/nginx/backend.oldpersiangames.org/opgbackend/storage/app/game-photos/' . $g->id . '/'))
                    mkdir('/usr/share/nginx/backend.oldpersiangames.org/opgbackend/storage/app/game-photos/' . $g->id . '/', 0777, true);
                $photos = [];
                foreach ($game['photos'] as $index => $photo) {

                    // copy('/archive/tmp/oldpersiangames.github.io/src/games/' . $game['id'] . '/photos/' . $photo['path'], '/archive/tmp/opgbackend/storage/app/game-photos/' . $g->id . '/' . ($index + 1) . '.jpg');
                    copy('/oldpersiangames.github.io/src/games/' . $game['id'] . '/photos/' . $photo['path'], '/usr/share/nginx/backend.oldpersiangames.org/opgbackend/storage/app/game-photos/' . $g->id . '/' . ($index + 1) . '.jpg');
                    $photos[] = [
                        "id" => $index + 1,
                        "type" => $photo['type'],
                        "uploaded_at" => Carbon::now(),
                    ];
                }
                $g->photos = $photos;
                $g->timestamps = false;
                $g->save();
            }


            $producers = Company::whereIn('slug', $game['producer'])->pluck('id');
            $publishers = Company::whereIn('slug', $game['publisher'])->pluck('id');

            $g->producers()->attach($producers, ['relation' => 'producer']);
            $g->publishers()->attach($publishers, ['relation' => 'publisher']);
        }

        foreach ($tgfiles as $tgfile) {
            if (isset($tgfile['document'])) {
                TGFile::insertOrIgnore([
                    "chat_id" => $tgfile['chat']['id'],
                    "message_id" => $tgfile['id'],
                    "file_id" => $tgfile['document']["file_id"],
                    "file_unique_id" => $tgfile['document']["file_unique_id"],
                    "file_name" => $tgfile['document']["file_name"],
                    "mime_type" => $tgfile['document']["mime_type"],
                    "file_size" => $tgfile['document']["file_size"],
                    "date" => $tgfile['document']["date"],
                ]);
            }
        }
    }
}
