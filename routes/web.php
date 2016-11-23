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
    
	Route::get('/{survey}',['as' => 'projects.voters.index', 'uses' => 'ProjectResultsController@index']);

	Route::get('/{voter}/create',['as' => 'projects.voters.create', 'uses' => 'ProjectResultsController@create']);

	Route::post('/{voter}',['as' => 'projects.voters.save', 'uses' => 'ProjectResultsController@save']);

	Route::get('/{voter}/results/{result}',['as' => 'projects.voters.results.show', 'uses' => 'ProjectResultsController@show']);

	Route::get('/{voter}/results/{result}/edit',['as' => 'projects.voters.results.edit', 'uses' => 'ProjectResultsController@edit']);

	Route::match(['put', 'patch'],'/{voter}/results/{result}',['as' => 'projects.voters.results.update', 'uses' => 'ProjectResultsController@update']);

});



Route::resource('projects', 'ProjectController');

Route::resource('questions', 'QuestionController');

Route::get('voters/search',[ 'as' => 'voters.search', 'uses'=>'VoterController@search']);

Route::resource('voters', 'VoterController');