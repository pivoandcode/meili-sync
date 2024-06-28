<?php

namespace PivoAndCode\MeiliSync\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Meilisearch;

class AddDocumentJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $document, private string $index){}

    public function handle(){
        Log::warning(json_encode( $this->document ));

        $test = $this->getOrCreateIndex($this->index)
             ->addDocumentsJson(json_encode([$this->document]), 'ID');

        Log::warning(json_encode($test));
    }

    private function getOrCreateIndex(string $index): Indexes {
        $client = app()->make(Client::class);

        $meiliIndex = collect($client->getIndexes()->getResults())->first(function(Indexes $remoteIndex) use ($index){
            return $remoteIndex->getUid() == $index;
        });

        if ( $meiliIndex ){
            return $meiliIndex;
        }

        $task = $client->createIndex($index);

        $client->waitForTask($task['taskUid']);

        return $client->getIndex($index);
    }
}
