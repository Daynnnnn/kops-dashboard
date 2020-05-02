<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/kops/create', 'KopsCommands@create');
Route::post('/kops/delete', 'KopsCommands@delete');

Route::post('/iam/create', 'IAM@create');
Route::post('/iam/delete', 'IAM@delete');

Route::post('/iam/get', 'IAM@get');