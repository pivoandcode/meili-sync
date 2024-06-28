<?php

use Illuminate\Support\Facades\Route;
use PivoAndCode\WordpressMeilisearchSync\Http\Controllers\PostsController;

Route::prefix( 'meilisearch' )->group( function () {
    Route::get( 'posts', [ PostsController::class, 'index' ] );
} );
