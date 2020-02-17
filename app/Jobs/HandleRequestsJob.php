<?php


namespace App\Jobs;

use App\Console\Commands\HandleRequests;
use App\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Database\QueryException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class HandleRequestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const REQUEST_TIMEOUT = 3;

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

        $ids = Request::query()
            ->limit(HandleRequests::CHUNK_SIZE)
            ->where('status', '=', Request::STATUS_NEW)
            ->orderBy('id')
            ->pluck('id');

        Request::query()->whereIn('id', $ids)->update(['status' => Request::STATUS_PROCESSING]);

        try {
            DB::beginTransaction();

            $requests = Request::query()->whereIn('id', $ids)->get()->keyBy('url');

            $results = [];
            foreach ($requests as $request) {
                $url = $request->url;
                try {
                    $statusCode = $this->httpClient->request('GET', $url, [
                        'timeout' => self::REQUEST_TIMEOUT, 
                    ])->getStatusCode();
                } catch (\Exception $exception) {
                    $statusCode = $exception->getCode();
                }
                $results[$url] = [
                    'id' => $requests[$url]['id'],
                    'url' => $url,
                    'status' => $statusCode === 200 ? Request::STATUS_DONE : Request::STATUS_ERROR,
                    'http_code' => $statusCode
                ];
            }
            Request::insertOnDuplicateKey($results);
        } catch (QueryException $exception) {
            DB::rollBack();
            \Log::info(__CLASS__ . ':' . $exception->getMessage());
            return;
        }

        DB::commit();
    }
}