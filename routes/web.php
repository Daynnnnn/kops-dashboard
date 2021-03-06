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

Route::post('/api/kops/get/ig', 'KopsCommands@getInstanceGroups');

Route::post('/api/iam/put/create', 'IAM@create');
Route::post('/api/iam/put/delete', 'IAM@delete');