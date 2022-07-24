<?php

namespace App\Console\Commands\Proxy;

use App\UseCase\Proxy\Checker;
use Illuminate\Console\Command;

class Check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:check {searchSource} {proxySource?}';

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
        try {
            $searchSource = $this->argument('searchSource');
            $proxySource = $this->argument('proxySource');

            $this->checker->check($searchSource, $proxySource, $this->output);
            $this->output->success('Прокси для источника ' . $searchSource . ' успешно обновлены');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->output->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
