<?php

namespace App\Console\Commands;

use App\Jobs\HandleRequestsJob;
use App\Request;
use Illuminate\Console\Command;

class HandleRequests extends Command
{
    const CHUNK_SIZE = 5;
    const QUEUE_NAME = 'default';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'handle:requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle urls from requests table';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $this->info('Processing...');

        $count = Request::query()->count();
        $offset = 0;

        while ($offset < $count) {
            $job = new HandleRequestsJob($offset);
            dispatch($job)->onQueue(self::QUEUE_NAME);
            $offset += self::CHUNK_SIZE;
        }

        $this->info('The jobs are generated!');
    }
}
