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

Route::get('/', function () {
    return view('welcome');
});

// Uses macros in RouteServiceProvider
Route::webhooksPost('/mailchimp', 'mailchimp');
Route::webhooksGet('/mailchimp', 'mailchimp');
Route::xeroPost('/xero', 'xero');
Route::xeroGet('/xero', 'xero');
Route::get('/payload', 'DummyPayloadController@payload');

