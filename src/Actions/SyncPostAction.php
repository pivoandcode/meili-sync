<?php

namespace PivoAndCode\MeiliSync\Actions;

use PivoAndCode\MeiliSync\Jobs\AddDocumentJob;
use WP_Post;

class SyncPostAction {
    public function __construct() {}

    public function handle(\WP_Post $post){
        if (! $this->isPostEligibleForSync($post) ) {
            return;
        }

        if (! $this->isPostTypeEligibleForSync($post->post_type) ){
            return;
        }

        AddDocumentJob::dispatch(
            document: apply_filters("meili_sync_convert_{$post->post_type}_to_document", $post->to_array()),
            index: $post->post_type
        );
    }

    private function isPostEligibleForSync( WP_Post $post ): bool {
        return (
            wp_is_post_revision($post->ID) ||
            wp_is_post_autosave($post->ID) ||
            $post->post_status != 'auto-draft'
        );
    }

    private function isPostTypeEligibleForSync( string $type ): bool {
        return in_array(
            $type,
            apply_filters( 'meili_sync_should_index_post_type', get_post_types() )
        );
    }
}
