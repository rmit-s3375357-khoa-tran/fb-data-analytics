<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;

use App\Http\Requests;
use Illuminate\Http\Request;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

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


        /*****Analysing sentiments******/
        $sentiments = $this->sentimentAnalysis($results);

        return view('twitter', compact('keyword', 'results', 'sentiments'));
    }

    private function sentimentAnalysis($results)
    {
        $sentiment_counter = array("positive"=>0, "negative"=>0, "neutral"=>0);

        $body_message = '{ "documents" : [';
        foreach ($results as $index=>$result)
        {
            $message = $result->text;
            $message = str_replace('"', "'", $message);
            $id = $index +1;
            $body_message .= '{ "language": "en", 
                                "id": '.$id.',
                                "text": "'.$message.'"},';
        }
        $body_message .= "]}";
        $client = new Client(); //GuzzleHttp\Client

        $res = $client->request('POST', 'https://westus.api.cognitive.microsoft.com/text/analytics/v2.0/sentiment', [
            'headers' => [
                'content-type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => env('AZURE_KEY_1')
            ],

            'body' => $body_message
        ]);

        $return_result = "";
        if  ($res->getStatusCode() == 200)
        {
            $return_result = $res->getBody();
        }


        return $return_result;
    }
}
