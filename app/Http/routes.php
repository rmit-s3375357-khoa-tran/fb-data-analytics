<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


/*
|--------------------------------------------------------------------------
| General Routes
|--------------------------------------------------------------------------
*/
Route::auth();
Route::get('/', [
    'middleware' => 'auth',
    function () {
        return view('pages.home');
    }
])->name('home');

Route::get('api/empty/results', 'ApiController@emptyResultsDirectory')->name('reset');
Route::get('api/analyse/{keyword}/{stopwords}', 'ApiController@analyse');

/*
|--------------------------------------------------------------------------
| Twitter Routes
|--------------------------------------------------------------------------
*/
Route::post('twitter/api/collect', 'TwitterApiController@collect');
Route::post('twitter/api/stream', 'TwitterApiController@stream');

/*
|--------------------------------------------------------------------------
| Youtube Routes
|--------------------------------------------------------------------------
*/
Route::post('youtube/api/search', 'YoutubeApiController@search');
Route::post('youtube/api/collect', 'YoutubeApiController@collect');
Route::post('youtube/api/addCustomUrls', 'YoutubeApiController@addCustomUrls');

/*
|--------------------------------------------------------------------------
| Facebook Routes
|--------------------------------------------------------------------------
*/
Route::post('facebook/api/collect', 'FacebookApiController@collect');

/*
|--------------------------------------------------------------------------
| Azure API Key Routes
|--------------------------------------------------------------------------
*/
Route::get('azure', [
    'middleware' => 'auth',
    function () {
        return view('pages.azure');
    }
]);
Route::post('azure/update', 'AzureApiKeyController@update');