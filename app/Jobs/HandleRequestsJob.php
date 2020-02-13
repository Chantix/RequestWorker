<?php


namespace App\Jobs;

use App\Console\Commands\HandleRequests;
use App\Request;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandleRequestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $offset;
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * HandleRequestsJob constructor.
     * @param int $offset
     */
    public function __construct(int $offset)
    {
        $this->offset = $offset;
    }

    public function handle()
    {
        $this->httpClient = new Client();

        $query = Request::query()
            ->where('status', '=', Request::STATUS_NEW)
            ->offset($this->offset)
            ->limit(HandleRequests::CHUNK_SIZE);

        $query->update(['status' => Request::STATUS_PROCESSING]);

        $requests = $query->get()->keyBy('url');

        $results = [];
        foreach ($requests as $request) {
            $url = $request->url;
            $statusCode = $this->httpClient->request('GET', $url)->getStatusCode();
            $results[$url] = [
                'id' => $requests[$url]['id'],
                'url' => $url,
                'status' => $statusCode === 200 ? Request::STATUS_DONE : Request::STATUS_ERROR,
                'http_code' => $statusCode
            ];
        }

        Request::insertOnDuplicateKey($results);
    }
}