<?php

namespace PivoAndCode\MeiliSync\Console;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Meilisearch\Client;
use PivoAndCode\MeiliSync\Actions\DeleteIndexAction;
use PivoAndCode\MeiliSync\Jobs\DeleteIndexJob;
use Roots\Acorn\Console\Commands\Command;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\spin;

class MeiliDeleteCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:delete
                            {postType : The Post Type that needs to be deleted from Meili}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes an index from Meilisearch';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $postType = $this->argument('postType');

        $confirm = confirm("Are you sure you want to delete the post type $postType");

        if (! $confirm ){
            $this->line("Okay, exiting the command.");
            return;
        }

        DeleteIndexJob::dispatch($postType);

        $this->line("Deleting post type $postType is enqueued.");
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'postType' => fn() => select(
                label: "Which post type would you like to delete from Meili?",
                scroll: 5,
                options: get_post_types()
            ),
        ];
    }
}
