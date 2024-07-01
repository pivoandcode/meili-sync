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
use PivoAndCode\MeiliSync\Actions\DeleteIndexAction;

class DeleteIndexJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $index){}

    public function handle(){
        app()->make(DeleteIndexAction::class)->handle($this->index);
    }
}
