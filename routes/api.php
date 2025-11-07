<?php

use App\Http\Controllers\Api\V1\ArticleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::get('articles', [ArticleController::class, 'index']);
});
