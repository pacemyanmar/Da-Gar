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
    if (Auth::check()) {
        if (Auth::user()->role->role_name == 'trainer') {
            return redirect('smsLogs');
        } else {
            return redirect('projects');
        }
    } else {
        return redirect('projects');
    }

});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::group(['prefix' => 'projects/{project}'], function () {
    Route::match(['get', 'post'], '/response/{filter}/{type?}', ['as' => 'projects.response.filter', 'uses' => 'SurveyResultsController@responseRateSample']);
    Route::match(['get', 'post'], '/double', ['as' => 'projects.response.double', 'uses' => 'SurveyResultsController@responseRateDouble']);
    Route::match(['get', 'post'], '/useorigin/{survey_id}/{column}', ['as' => 'projects.response.origin.use', 'uses' => 'SurveyResultsController@originUse']);
    Route::match(['get', 'post'], '/usedouble/{survey_id}/{column}', ['as' => 'projects.response.double.use', 'uses' => 'SurveyResultsController@doubleUse']);
    Route::get('/search/sample', ['as' => 'projects.sample.search', 'uses' => 'ProjectController@search']);
    Route::get('/sampledata/{sampledata}/form/{formid}', ['as' => 'projects.incident.create', 'uses' => 'ProjectController@addIncident']);
    Route::get('/analysis', ['as' => 'projects.analysis', 'uses' => 'SurveyResultsController@analysis']);
    Route::match(['get','post'],'/smslog', ['as' => 'projects.smslog', 'uses' => 'ProjectController@smslog']);
    Route::get('/export', ['as' => 'projects.export', 'uses' => 'ProjectController@export']);
    Route::post('/import', ['as' => 'projects.import', 'uses' => 'ProjectController@import']);
    Route::get('/create-views', ['as' => 'projects.createviews', 'uses' => 'ProjectController@createAllViews']);
    Route::post('/trainingmode', ['as' => 'projects.trainingmode', 'uses' => 'ProjectController@trainingmode']);
    Route::post('/addlogic', ['as' => 'projects.logic', 'uses' => 'ProjectController@addLogic']);
    Route::post('/upload', ['as' => 'projects.upload.samples', 'uses' => 'ProjectController@uploadSamples']);
    Route::match(['get','post'],'/getcsv', ['as' => 'projects.getcsv', 'uses' => 'API\SmsAPIController@getcsv']);
    Route::get('/channel-rate', ['as' => 'projects.channel.rates', 'uses' => 'ProjectController@channelRates']);

    Route::get('location-metas/show-structure', ['as' => 'location-metas.show-structure', 'uses' => 'LocationMetaController@editStructure']);

    Route::match(['post','patch'],'location-metas/edit-structure', ['as' => 'location-metas.edit-structure', 'uses' => 'LocationMetaController@createOrUpdateStructure']);

    Route::resource('sample-details', 'SampleDetailsController');
});

Route::group(['prefix' => 'projects/{project}/surveys'], function () {

    Route::match(['get', 'post'], '/{sample_type?}', ['as' => 'projects.surveys.index', 'uses' => 'SurveyResultsController@index']);

    Route::get('/{result}/create/{form_id?}/{sample_type?}', ['as' => 'projects.surveys.create', 'uses' => 'SurveyResultsController@create']);

    Route::post('/{result}/save/{form_id?}/{sample_type?}', ['as' => 'projects.surveys.save', 'uses' => 'SurveyResultsController@save']);

    Route::get('/{result}', ['as' => 'projects.surveys.show', 'uses' => 'SurveyResultsController@show']);

    // Route::get('/{sample_id}/edit', ['as' => 'projects.surveys.edit', 'uses' => 'SurveyResultsController@edit']);

    Route::match(['put', 'patch'], '/{result}', ['as' => 'projects.surveys.update', 'uses' => 'SurveyResultsController@update']);

    Route::delete('/{result}', ['as' => 'projects.surveys.destroy', 'uses' => 'SurveyResultsController@destroy']);

});

Route::post('projects/{project}/dbcreate', ['as' => 'projects.dbcreate', 'uses' => ProjectController::class . '@dbcreate']);

Route::get('projects/sort/{project}', ['as' => 'projects.sort', 'uses' => ProjectController::class . '@sort']);

Route::get('projects/migrate', ['as' => 'projects.migrate', 'uses' => ProjectController::class . '@migrate']);

Route::post('projects/import', ['as' => 'projects.import', 'uses' => ProjectController::class . '@import']);


Route::resource('projects', 'ProjectController');

Route::post('questions', ['as' => 'questions.store', 'uses' => QuestionController::class . '@store']);

Route::post('questions/sort', ['as' => 'questions.sort', 'uses' => QuestionController::class . '@sort']);

Route::match(['put', 'patch'], 'questions/{question}', ['as' => 'questions.update', 'uses' => QuestionController::class . '@update']);

Route::delete('questions/{question}', ['as' => 'questions.destroy', 'uses' => QuestionController::class . '@destroy']);

//Route::resource('questions', 'QuestionController');

Route::resource('smsLogs', 'SmsLogController');

Route::match(['get','post'], 'smslogs', ['as' => 'smsLogs.index', 'uses' => 'SmsLogController@index']);

Route::post('translate/{id}', ['as' => 'translate', 'uses' => 'SettingController@translate']);

Route::post('settings/save', ['as' => 'settings.save', 'uses' => 'SettingController@save']);

Route::resource('settings', 'SettingController');

Route::post('sample/import', ['as' => 'sample.import', 'uses' => 'SampleDataController@import']);

Route::resource('sampleDatas', 'SampleDataController');

Route::resource('users', 'UserController');

Route::resource('roles', 'RoleController');

Route::resource('projectPhones', 'ProjectPhoneController');

Route::resource('observers', 'ObserverController');

Route::resource('logicalChecks', 'LogicalCheckController');


Route::resource('location-metas', 'LocationMetaController');

Route::post('translations/import',['as' => 'translations.import', 'uses' => 'TranslationController@import']);

Route::resource('translations', 'TranslationController');

