<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');
// Route::resource('projects', 'ProjectAPIController');

Route::match(['get', 'post'], 'telerivet', ['as' => 'telerivet', 'uses' => 'SmsAPIController@telerivet']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::resource('sms', 'SmsAPIController');
});
