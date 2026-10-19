<?php

use Ichynul\Configx\Http\Controllers\ConfigxController;

Route::redirect('configx', 'configx/edit/0');
Route::get('configx/edit/{id?}', ConfigxController::class.'@edit');
Route::post('configx/saveall/{id?}', ConfigxController::class.'@saveall');
Route::put('configx/sort', ConfigxController::class.'@postSort');
Route::put('configx/delfile/{id}', ConfigxController::class.'@delFile');