<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Item;
use Illuminate\Console\Command;

/* Not used anymore */

class InsertMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:insert-media';

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
        $items = Item::get();
        foreach ($items as $item) {
            if ($item->photos)
                foreach ($item->photos as $photo) {
                    $item->addMediaFromDisk('item-photos/' . $item->id . '/' . $photo['id'] . '.jpg', 'public')->usingFileName(str_replace('/tmp/', '', tempnam(sys_get_temp_dir(), 'media-library')))->preservingOriginal()->toMediaCollection();
                }
        }

        $games = Game::get();
        foreach ($games as $game) {
            if ($game->photos)
                foreach ($game->photos as $photo) {
                    $game->addMediaFromDisk('game-photos/' . $game->id . '/' . $photo['id'] . '.jpg', 'public')->usingFileName(str_replace('/tmp/', '', tempnam(sys_get_temp_dir(), 'media-library')))->preservingOriginal()->toMediaCollection();
                }
        }
    }
}
