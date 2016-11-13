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


Route::get('projects/{project}/voters/{voter}',['as' => 'projects.voters.index', 'uses' => 'ProjectVoterController@index']);

Route::get('projects/{project}/voters/{voter}/create',['as' => 'projects.voters.create', 'uses' => 'ProjectVoterController@create']);

Route::post('projects/{project}/voters/{voter}',['as' => 'projects.voters.save', 'uses' => 'ProjectVoterController@save']);

Route::get('projects/{project}/voters/{voter}/results/{result}',['as' => 'projects.voters.results.show', 'uses' => 'ProjectVoterController@show']);

Route::get('projects/{project}/voters/{voter}/results/{result}/edit',['as' => 'projects.voters.results.edit', 'uses' => 'ProjectVoterController@edit']);

Route::match(['put', 'patch'],'projects/{project}/voters/{voter}/results/{result}',['as' => 'projects.voters.results.update', 'uses' => 'ProjectVoterController@update']);


Route::resource('projects', 'ProjectController');

Route::resource('questions', 'QuestionController');

Route::get('voters/search',[ 'as' => 'voters.search', 'uses'=>'VoterController@search']);

Route::resource('voters', 'VoterController');