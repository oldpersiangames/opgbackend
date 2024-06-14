<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class UpdateDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-db';

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
        Artisan::call('db:wipe');
        $result = Process::run("cd /opgactions/opg-backups/ && git pull");
        // echo $result->output();
        // echo $result->errorOutput();
        $result = Process::timeout(600)->run("mysql -u root -p1 opg < /opgactions/opg-backups/opgbackend.sql");
    }
}
