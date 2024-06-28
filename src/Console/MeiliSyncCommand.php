<?php

namespace PivoAndCode\MeiliSync\Console;

use PivoAndCode\MeiliSync\Actions\SyncPostAction;
use Roots\Acorn\Console\Commands\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;

class MeiliSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili-sync {postType?}';

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

        $count = collect((array) wp_count_posts($postType))->sum(fn($status) => $status);
        $offset = 0;

        $bar = $this->output->createProgressBar($count);

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');

        $bar->setFormat('custom');
        $bar->setMessage("Starting the reindex for the $postType post type.");
        $bar->start();

        $posts = new \WP_Query([
           'post_type' => 'any',
           'posts_per_page' => 500,
           'offset' => $offset
        ]);

        while($posts->have_posts()){
            foreach ( $posts->get_posts() as $key => $post ){
                sleep(1);

                app()->make(SyncPostAction::class)
                     ->handle($post);

                $message = sprintf(
                    '%d posts remaining',
                   $count - ($offset + $key) - 1
                );

                $bar->setMessage($message);
                $bar->advance();
            }

            $offset =+ 500;

            $posts = new \WP_Query([
                'post_type' => 'any',
                'posts_per_page' => 500,
                'offset' => $offset
            ]);
        }

        $bar->setMessage("Finished");
        $bar->finish();
    }
}
