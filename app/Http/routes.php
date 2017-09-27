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
Route::get('/', function () {
    return view('pages.home');
});

Route::get('api/empty/results', 'ApiController@emptyResultsDirectory');

/*
|--------------------------------------------------------------------------
| Twitter Routes
|--------------------------------------------------------------------------
*/
Route::post('twitter/api/collect', 'TwitterApiController@collect');

/*
|--------------------------------------------------------------------------
| Youtube Routes
|--------------------------------------------------------------------------
*/
Route::post('youtube/api/search','YoutubeApiController@search');
Route::post('youtube/api/collect','YoutubeApiController@collect');
Route::post('youtube/api/addCustomUrls','YoutubeApiController@addCustomUrls');

/*
|--------------------------------------------------------------------------
| Facebook Routes
|--------------------------------------------------------------------------
*/
Route::post('facebook/api/collect','FacebookApiController@search');