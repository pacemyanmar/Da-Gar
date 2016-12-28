<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
 */

Route::get('/', function () {
    return redirect('home');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::group(['prefix' => 'projects/{project}/surveys'], function () {

    Route::match(['get', 'post'], '/{sample_type?}', ['as' => 'projects.surveys.index', 'uses' => 'ProjectResultsController@index']);

    Route::get('/{result}/create/{form_id?}/{sample_type?}', ['as' => 'projects.surveys.create', 'uses' => 'ProjectResultsController@create']);

    Route::post('/{result}/save/{form_id?}/{sample_type?}', ['as' => 'projects.surveys.save', 'uses' => 'ProjectResultsController@save']);

    Route::get('/{result}', ['as' => 'projects.surveys.show', 'uses' => 'ProjectResultsController@show']);

    // Route::get('/{sample_id}/edit', ['as' => 'projects.surveys.edit', 'uses' => 'ProjectResultsController@edit']);

    Route::match(['put', 'patch'], '/{result}', ['as' => 'projects.surveys.update', 'uses' => 'ProjectResultsController@update']);

    Route::delete('/{result}', ['as' => 'projects.surveys.destroy', 'uses' => 'ProjectResultsController@destroy']);

});

Route::post('projects/{project}/dbcreate', ['as' => 'projects.dbcreate', 'uses' => ProjectController::class . '@dbcreate']);

Route::resource('projects', 'ProjectController');

Route::resource('questions', 'QuestionController');

Route::get('voters/search', ['as' => 'voters.search', 'uses' => 'VoterController@search']);

Route::resource('voters', 'VoterController');

Route::resource('smsLogs', 'SmsLogController');

Route::post('settings/save', ['as' => 'settings.save', 'uses' => 'SettingController@save']);

Route::resource('settings', 'SettingController');


Route::resource('locations', 'LocationController');