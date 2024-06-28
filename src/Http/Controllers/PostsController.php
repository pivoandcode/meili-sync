<?php

namespace PivoAndCode\MeiliSync;

use Illuminate\Http\Request;
use PivoAndCode\MeiliSync\Actions\RenderMeiliProductsAction;

class PostsController {
    public function index( Request $request, RenderMeiliProductsAction $getProductsAction) {
        return response()->json();
    }
}
