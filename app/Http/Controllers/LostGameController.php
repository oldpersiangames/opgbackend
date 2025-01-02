<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LostGameController extends Controller
{


    public function index()
    {
        return str_replace("lost-games/", "", Storage::disk('public')->files('lost-games'));
    }

    public function store(Request $request)
    {
        foreach ($request->file('files') as $file) {
            $file->storeAs('lost-games', $file->getClientOriginalName(), 'public');
        }
    }

    public function rename(Request $request)
    {
        return rename(config('filesystems.disks.public.root') . '/lost-games/' . $request->old_filename, config('filesystems.disks.public.root') . '/lost-games/' . $request->new_filename);
        // return Storage::disk('public')->move("lost-games/$request->old_filename", "lost-games/$request->new_filename");
    }

    public function destroy($filename)
    {
        return unlink(config('filesystems.disks.public.root') . '/lost-games/' . $filename);
        // return Storage::disk('public')->delete("lost-games/$filename");
    }
}
