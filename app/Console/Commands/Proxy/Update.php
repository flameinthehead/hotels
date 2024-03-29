<?php

namespace App\Console\Commands\Proxy;

use App\Models\Proxy;
use App\UseCase\Proxy\Parser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use function config;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:update {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление прокси из источников config/proxy';

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
        try {
            $sources = config('proxy.sources');
            $source = $this->argument('source');

            if (!empty($source)) {
                if(!isset($sources[$source])){
                    $this->error('Неизвестный код прокси');
                    return self::FAILURE;
                }
                $this->parser->update(App::make($sources[$source]));
            } else {
                $bar = $this->output->createProgressBar(count($sources));
                foreach ($sources as $sourceClass) {
                    $bar->advance();
                    $this->parser->update(App::make($sourceClass));
                }
                $bar->finish();
            }
            $this->info('Прокси обновлены');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }

        return true;
    }
}
