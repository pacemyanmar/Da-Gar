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
    return redirect('projects');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::group(['prefix' => 'projects/{project}'], function () {
    Route::match(['get', 'post'], '/response/{filter}', ['as' => 'projects.response.filter', 'uses' => 'ProjectResultsController@responseRateSample']);
    Route::match(['get', 'post'], '/double/{section}', ['as' => 'projects.response.double', 'uses' => 'ProjectResultsController@responseRateDouble']);
    Route::match(['get', 'post'], '/useorigin/{survey_id}/{column}', ['as' => 'projects.response.origin.use', 'uses' => 'ProjectResultsController@originUse']);
    Route::match(['get', 'post'], '/usedouble/{survey_id}/{column}', ['as' => 'projects.response.double.use', 'uses' => 'ProjectResultsController@doubleUse']);
});

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

Route::get('projects/sort/{project}', ['as' => 'projects.sort', 'uses' => ProjectController::class . '@sort']);

Route::resource('projects', 'ProjectController');

Route::post('questions', ['as' => 'questions.store', 'uses' => QuestionController::class . '@store']);

Route::post('questions/sort', ['as' => 'questions.sort', 'uses' => QuestionController::class . '@sort']);

Route::match(['put', 'patch'], 'questions/{question}', ['as' => 'questions.update', 'uses' => QuestionController::class . '@update']);

Route::delete('questions/{question}', ['as' => 'questions.destroy', 'uses' => QuestionController::class . '@destroy']);

//Route::resource('questions', 'QuestionController');

Route::resource('smsLogs', 'SmsLogController');

Route::post('translate/{id}', ['as' => 'translate', 'uses' => 'SettingController@translate']);

Route::post('settings/save', ['as' => 'settings.save', 'uses' => 'SettingController@save']);

Route::resource('settings', 'SettingController');

Route::post('sample/import', ['as' => 'sample.import', 'uses' => 'SampleDataController@import']);

Route::post('sample/import/translation', ['as' => 'sample.importTranslation', 'uses' => 'SampleDataController@importTranslation']);

Route::resource('sampleDatas', 'SampleDataController');

Route::resource('users', 'UserController');

Route::resource('roles', 'RoleController');
