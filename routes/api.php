<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('{category}', [ApiController::class, 'dataCategory']);
Route::get('request/api', [ApiController::class, 'requestExternalApi']);

