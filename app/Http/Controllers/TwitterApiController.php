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

        //print_r($results);

        /*****Analysing sentiments******/
        $sentiments = $this->sentimentAnalysis($results);
        list($tweetSentiments, $coordinates) = $this->analyseTweet($results);

        return view('twitter', compact('keyword', 'results', 'sentiments', 'tweetSentiments','coordinates'));
    }


// ##### DatumboxAPI sentiment analysis #####
    private function analyseTweet($results)
    {
        require_once('DatumboxAPI.php');
        $DatumboxAPI = new DatumboxAPI(env('DATUM_BOX_API'));
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        $location = array();

        echo "<br> DATUMBOX <br>";
        foreach ($results as $index => $result) {
            $message = $result->text;
            $message = str_replace('@', "", $message);
            $message = $DatumboxAPI->TwitterSentimentAnalysis($message);

            echo "id: " . ($index + 1) . " value: " . $message . "<br>";

            if ($message == "negative") {
                $sentiment_counter['negative']++;
            } elseif ($message == "positive") {
                $sentiment_counter['positive']++;
            } else {
                $sentiment_counter['neutral']++;
            }



            // Get lat and long by address
            //print_r($result->user->time_zone);
            //echo"<br>";
            if (array_key_exists("time_zone",$result->user))
            {
                $address = $result->user->time_zone; // Google HQ
                if ($address != null){
                    $prepAddr = str_replace(' ','+',$address);
                    $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
                    $output= json_decode($geocode);
                    $latitude = $output->results[0]->geometry->location->lat;
                    $longitude = $output->results[0]->geometry->location->lng;
                    array_push($location,array("lat"=>$latitude,"lng"=>$longitude));
                }

            }
        }

        return [$sentiment_counter, $location];

    }

// ##### AzureAPI sentiment analysis #####
    private function sentimentAnalysis($results)
    {

        /* Converting messages into json body for request*/
        $body_message = '{ "documents" : [';
        foreach ($results as $index => $result) {
            $message = $result->text;
            $message = str_replace('"', "'", $message);
            $id = $index + 1;
            $body_message .= '{ "language": "en", 
                                "id": ' . $id . ',
                                "text": "' . $message . '"},';
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
        if ($res->getStatusCode() == 200) {
            $json = $res->getBody();
        }

        /* Parsing results and incrementing a sentiment counter*/
        $results = json_decode($json, true);
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);

        echo "AZURE" . "<br>";
        foreach ($results['documents'] as $result) {
            $score = $result['score'];
            $id = $result['id'];

            if ($score < 0.5) {
                $sentiment_counter['negative']++;
                echo "id: " . ($id) . " value: negative<br>";
            } elseif ($score > 0.5) {
                $sentiment_counter['positive']++;
                echo "id: " . ($id) . " value: positive<br>";
            } else {
                $sentiment_counter['neutral']++;
                echo "id: " . ($id) . " value: neutral<br>";
            }
        }

        return $sentiment_counter;
    }




}
