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
        $tweetSentiments = $this->tweetSentimentAnalysis($results);

        return view('twitter', compact('keyword', 'results', 'sentiments', 'tweetSentiments'));
    }


    private function tweetSentimentAnalysis($results)
    {
        require_once('DatumboxAPI.php');

        $DatumboxAPI = new DatumboxAPI(env('DATUM_BOX_API'));
        $sentiment_counter = array("positive"=>0, "negative"=>0, "neutral"=>0);

        $analysis = array();

        echo "<br> DATUMBOX <br>";
        foreach ($results as $index=>$result)
        {
            $analysis[$index + 1] = $DatumboxAPI->SentimentAnalysis($result->text);
            $value = $analysis[$index + 1];

            echo "id: ".($index + 1)." value: ".$value."<br>";

            if ($value=="negative")
            {
                $sentiment_counter['negative']++;
            }
            elseif ($value=="positive")
            {
                $sentiment_counter['positive']++;
            }
            else
            {
                $sentiment_counter['neutral']++;
            }

        }

        return $sentiment_counter;

    }

    private function sentimentAnalysis($results)
    {

        /* Converting messages into json body for request*/
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

        /*Creating a request*/
        $client = new Client(); //GuzzleHttp\Client
        $res = $client->request('POST', 'https://westus.api.cognitive.microsoft.com/text/analytics/v2.0/sentiment', [
            'headers' => [
                'content-type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => env('AZURE_KEY_1')
            ],
            'body' => $body_message
        ]);

        /*Response returned*/
        $json = "";
        if  ($res->getStatusCode() == 200)
        {
            $json = $res->getBody();
        }

        /* Parsing results and incrementing a sentiment counter*/
        $results = json_decode($json,true);
        $sentiment_counter = array("positive"=>0, "negative"=>0, "neutral"=>0);

        echo "AZURE"."<br>";
        foreach ($results['documents'] as $result){
            $score =  $result['score'];
            $id = $result['id'];

            if ($score < 0.5)
            {
                $sentiment_counter['negative']++;
                echo "id: ".($id)." value: negative<br>";
            }
            elseif ($score > 0.5)
            {
                $sentiment_counter['positive']++;
                echo "id: ".($id)." value: positive<br>";
            }
            else
            {
                $sentiment_counter['neutral']++;
                echo "id: ".($id)." value: neutral<br>";
            }
            //echo $result['score']."<br>";
        }

        //echo $sentiment_counter['positive']."<br>";
        //echo $sentiment_counter['negative']."<br>";
        //echo $sentiment_counter['neutral'];


        //return $return_result;

        return $sentiment_counter;
    }
}
