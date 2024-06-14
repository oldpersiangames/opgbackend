<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use SergiX44\Nutgram\Nutgram;

class TelegramController extends Controller
{
    public function getFiles(Nutgram $bot)
    {
        $updates = $bot->getUpdates();
        return $updates;
    }
}
