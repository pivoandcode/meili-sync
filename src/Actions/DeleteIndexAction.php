<?php

namespace PivoAndCode\MeiliSync\Actions;

use Meilisearch\Client;

class DeleteIndexAction {
    public function __construct(private Client $client) {}

    public function handle(string $index): bool {
        $task = $this->client->deleteIndex($index);

        $this->client->waitForTask($task['taskUid']);

        return true;
    }
}
