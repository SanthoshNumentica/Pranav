<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', fn() => Redirect::to('/admin'));
Route::get('/viewer/mpr/{case}', fn() => 'MPR')->name('viewer.mpr');
Route::get('/viewer/stone/{case}', fn() => 'Stone')->name('viewer.stone');
Route::get('/viewer/standard/{case}', fn() => 'Standard')->name('viewer.standard');
Route::get('/viewer/volume/{case}', fn() => 'Volume')->name('viewer.volume');
Route::get('/viewer/segmentation/{case}', fn() => 'Segmentation')->name('viewer.segmentation');