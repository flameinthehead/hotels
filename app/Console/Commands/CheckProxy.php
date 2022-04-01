<?php

namespace App\Console\Commands;

use App\UseCase\Proxy\Checker;
use Illuminate\Console\Command;

class CheckProxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:check {source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка прокси на работоспособность';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private Checker $checker)
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
        $source = $this->argument('source');

        $this->checker->check($source);
        return 0;
    }
}
