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
    
	Route::get('/{sample_id?}',['as' => 'projects.surveys.index', 'uses' => 'ProjectResultsController@index']);

	Route::get('/{sample_id}/create/{sample_type?}',['as' => 'projects.surveys.result.create', 'uses' => 'ProjectResultsController@create']);

	Route::post('/{sample_id}',['as' => 'projects.surveys.result.save', 'uses' => 'ProjectResultsController@save']);

	Route::get('/{sample_id}/results/{result}',['as' => 'projects.surveys.result.show', 'uses' => 'ProjectResultsController@show']);

	Route::get('/{sample_id}/results/{result}/edit',['as' => 'projects.surveys.result.edit', 'uses' => 'ProjectResultsController@edit']);

	Route::match(['put', 'patch'],'/{sample_id}/results/{result}',['as' => 'projects.surveys.result.update', 'uses' => 'ProjectResultsController@update']);

	Route::delete('/{sample_id}/results/{result}',['as' => 'projects.surveys.result.destroy', 'uses' => 'ProjectResultsController@destroy']);

});



Route::resource('projects', 'ProjectController');

Route::resource('questions', 'QuestionController');

Route::get('voters/search',[ 'as' => 'voters.search', 'uses'=>'VoterController@search']);

Route::resource('voters', 'VoterController');