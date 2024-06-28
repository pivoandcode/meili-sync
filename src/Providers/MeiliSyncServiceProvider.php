<?php

namespace PivoAndCode\MeiliSync\Providers;

use Illuminate\Support\ServiceProvider;
use PivoAndCode\MeiliSync\Actions\SyncPostAction;
use PivoAndCode\MeiliSync\Console\MeiliSyncCommand;
use PivoAndCode\MeiliSync\MeiliSync;
use WP_Post;

require_once __DIR__ . '/../../vendor/autoload.php';

class MeiliSyncServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('MeiliSync', function () {
            return new MeiliSync($this->app);
        });

        $this->app->singleton(\Meilisearch\Client::class, function() {
            return new \Meilisearch\Client(
                config('meilisearch-sync.host'),
                config('meilisearch-sync.key')
            );
        });

        $this->mergeConfigFrom(
            __DIR__.'/../../config/meilisearch-sync.php',
            'meilisearch-sync'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (defined('WC_ABSPATH')) {
            $this->bindActions();
            $this->bindFilters();
        }

        $this->publishes([
            __DIR__.'/../../config/meilisearch-sync.php' => $this->app->configPath('meilisearch-sync.php'),
        ], 'config');

        $this->loadViewsFrom(
            __DIR__.'/../../resources/views',
            'meili',
        );

        $this->commands([
            \PivoAndCode\MeiliSync\Console\MeiliSyncCommand::class
        ]);

        $this->app->make('MeiliSync');
    }

    private function bindActions() {
        add_action('wp_insert_post', function(int $post_id, WP_Post $post, bool $update){
            app()->make(SyncPostAction::class)->handle($post);
        }, 10, 3);
    }

    private function bindFilters() {
    }
}
