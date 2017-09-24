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
    return view('pages.home');
});

Route::post('twitter/api/collect', 'TwitterApiController@collect');
Route::post('youtube/api/search','YoutubeApiController@search');
Route::post('youtube/api/collect','YoutubeApiController@collect');
Route::post('facebook/api/collect','FacebookApiController@search');

Route::get('api/empty/results', 'ApiController@emptyResultsDirectory');


Route::get('test', function(){
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
    set_time_limit(60 * 3);

    $youtubeAPIKey = env('YOUTUBE_API');
    $videos = array();

    /***** Getting top 3 youtube videos for keyword ****/
    /* make api request */
    $url = "https://www.googleapis.com/youtube/v3/search?key=" . $youtubeAPIKey .
        "&part=id,snippet&q=Trump&maxResults=" . "3" . "&type=video";

    $client = new \GuzzleHttp\Client();
    try {
        $apiResponse = $client->get($url);
    } catch (\Exception $e) {
        return $e->getMessage();
    }

    if ($apiResponse->getStatusCode() == 200)
    {
        $resp = (string)$apiResponse->getBody();
        $results = json_decode($resp);

        dd($results);

        if ($results) {
            foreach ($results->items as $searchResult) {
                array_push($videos, $searchResult->id->videoId);
            }
        }
    }
});

Route::get('test/carbon', function(){
    dd(\Carbon\Carbon::parse('yesterday'));
});