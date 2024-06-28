<?php

namespace PivoAndCode\MeiliSync\Console;

use Illuminate\Support\Facades\Artisan;
use PivoAndCode\MeiliSync\Actions\SyncPostAction;
use Roots\Acorn\Console\Commands\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;

class MeiliSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:sync {postType?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'My custom Acorn command.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $postType = $this->argument('postType');

        if ( is_null($postType) ){
            $postType = select(
                label: 'What post post do you want to sync?',
                options: get_post_types(),
                scroll: 5
            );
        }

        $confirm = confirm("Are you sure you want to reindex the post type $postType");

        if ( !$confirm ){
            return;
        }

        $count = collect((array) wp_count_posts($postType))->reduce(function($sum, $count, $key){
            if ( in_array($key, ['auto-draft', 'trash'])  ){
                return $sum;
            }

            return ((int) $count) + $sum;
        }, 0);

        $offset = 0;

        $progress = progress(
            label: "Starting the reindex for the $postType post type.",
            steps: $count
        );

        $progress->start();

        $posts = new \WP_Query([
            'post_type' => $postType,
            'posts_per_page' => 100,
            'post_status' => 'any',
            'offset' => $offset,
        ]);

        while($posts->have_posts()){
            foreach ( $posts->get_posts() as $key => $post ){
                app()->make(SyncPostAction::class)
                     ->handle($post);

                $message = sprintf(
                    '%d posts remaining',
                   $count - ($offset + $key) - 1
                );

                $progress->label($message);
                $progress->advance();
            }

            $offset =+ 100;

            $posts = new \WP_Query([
                'post_type' => $postType,
                'posts_per_page' => 500,
                'offset' => $offset
            ]);
        }

        $progress->label("Finished");
        $progress->finish();

        $startsWorkers = confirm("Do you want to start the queue worker in order to process the sync?", false);

        if (! $startsWorkers ){
            return;
        }

        spin(
            function(){ $this->callSilently('queue:work', ['--memory' => 1024, '--stop-when-empty' => true]); },
            'Workers are active...'
        );

        $this->line("Syncing for post type $postType finished.");
    }
}
