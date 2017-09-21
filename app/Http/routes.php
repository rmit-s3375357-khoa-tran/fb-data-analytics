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

Route::get('/', function () {
    return view('search');
});

Route::post('twitter/api/collect', 'TwitterApiController@search');
Route::post('youtube/api/collect','YoutubeApiController@search');
Route::post('facebook/api/collect','FacebookApiController@search');
