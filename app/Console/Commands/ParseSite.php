<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Parcer;

class ParseSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse {num?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse Command';

    /**
     * Execute the console command.
     */
    public function handle(Parcer $parcer)
    {
        $parcer->parcingLinks($this->argument('num'));
    }
}
