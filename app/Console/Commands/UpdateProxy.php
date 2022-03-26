<?php

namespace App\Console\Commands;

use App\UseCase\Proxy\Parser;
use App\UseCase\Proxy\Source\FreeProxy;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class UpdateProxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make update proxy';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private Parser $parser)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sources = config('proxy.sources');
        foreach ($sources as $sourceClass) {
            $this->parser->update(new $sourceClass(new Client()));
        }

        return 0;
    }
}
