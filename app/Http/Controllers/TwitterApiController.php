<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;

use App\Http\Requests;
use Illuminate\Http\Request;

class TwitterApiController extends Controller
{
    private $twitter;

    /**
     * TwitterApiController constructor.
     *
     * Set up authorised connection to twitter api
     */
    public function __construct()
    {
        $this->twitter = new TwitterOAuth(
            config('setting.twitter_oauth.customer_key'),
            config('setting.twitter_oauth.customer_secret'),
            config('setting.twitter_oauth.access_token'),
            config('setting.twitter_oauth.access_token_secret')
        );
        $this->twitter->get("account/verify_credentials");
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $keyword = isset($request->keyword) ? $request->keyword : 'twitterapi';
        $statuses = $this->twitter->get("search/tweets", ["q" => $keyword]);
        $results = $statuses->statuses;

        return view('twitter', compact('keyword', 'results'));
    }
}
