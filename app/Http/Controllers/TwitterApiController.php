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

    //API URL
    const API_URL ='http://maps.googleapis.com/maps/api/geocode/json';

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
     * Do call
     *
     * @return object
     * @param  array  $parameters
     */
    protected function doCall($parameters = array())
    {
        // check if curl is available
        if (!function_exists('curl_init')) {
            // throw error
            throw new GeolocationException('This method requires cURL (http://php.net/curl), it seems like the extension isn\'t installed.');
        }

        // define url
        $url = self::API_URL . '?';

        // add every parameter to the url
        foreach ($parameters as $key => $value) $url .= $key . '=' . urlencode($value) . '&';

        // trim last &
        $url = trim($url, '&');

        // init curl
        $curl = curl_init();

        // set options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // execute
        $response = curl_exec($curl);

        // fetch errors
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);

        // close curl
        curl_close($curl);

        // we have errors
        if ($errorNumber != '') throw new GeolocationException($errorMessage);

        // redefine response as json decoded
        $response = json_decode($response);

        // return the content
        return $response->results;
    }

    /**
     * Get address using latitude/longitude
     *
     * @return array(label, components)
     * @param  float        $latitude
     * @param  float        $longitude
     */
    public function getAddress($latitude, $longitude)
    {
        $addressSuggestions = $this->getAddresses($latitude, $longitude);

        return $addressSuggestions[0];
    }

    /**
     * Get possible addresses using latitude/longitude
     *
     * @return array(label, street, streetNumber, city, cityLocal, zip, country, countryLabel)
     * @param  float        $latitude
     * @param  float        $longitude
     */
    public function getAddresses($latitude, $longitude)
    {
        // init results
        $addresses = array();

        // define result
        $addressSuggestions = $this->doCall(array(
            'latlng' => $latitude . ',' . $longitude,
            'sensor' => 'false'
        ));

        // loop addresses
        foreach ($addressSuggestions as $key => $addressSuggestion) {
            // init address
            $address = array();

            // define label
            $address['label'] = isset($addressSuggestion->formatted_address) ?
                $addressSuggestion->formatted_address : null
            ;

            // define address components by looping all address components
            foreach ($addressSuggestion->address_components as $component) {
                $address['components'][] = array(
                    'long_name' => $component->long_name,
                    'short_name' => $component->short_name,
                    'types' => $component->types
                );
            }

            $addresses[$key] = $address;
        }

        return $addresses;
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
        // mapbox

        // Geotag

        // Some test coordinate
        // Melbourne coordinates (37.8136,144.963)
        // Ho Chi Minh city (10.8231,106.6297)
        // RMIT Melbourne (-37.804663448 , 144.957996168)

        $coordinate = $this->getAddress(-37.804663448 , 144.957996168);

        $coordinate["lon"] = -37.804663448;
        $coordinate["lat"] = 144.957996168;
        echo 'Address = ' . $coordinate['label'] . '<br/>';


        /*****Analysing sentiments******/
        $sentiments = $this->sentimentAnalysis($results);
        $tweetSentiments = $this->tweetSentimentAnalysis($results);

        return view('twitter', compact('keyword', 'results', 'sentiments', 'tweetSentiments','coordinate'));
    }


// ##### DatumboxAPI sentiment analysis #####
    private function tweetSentimentAnalysis($results)
    {
        require_once('DatumboxAPI.php');

        $DatumboxAPI = new DatumboxAPI(env('DATUM_BOX_API'));
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);

        $analysis = array();

        echo "<br> DATUMBOX <br>";
        foreach ($results as $index => $result) {
            $message = $result->text;
            //$message = str_replace('@', "", $message);
            $analysis[$index + 1] = $DatumboxAPI->TwitterSentimentAnalysis($message);
            $value = $analysis[$index + 1];

            echo "id: " . ($index + 1) . " value: " . $value . "<br>";

            if ($value == "negative") {
                $sentiment_counter['negative']++;
            } elseif ($value == "positive") {
                $sentiment_counter['positive']++;
            } else {
                $sentiment_counter['neutral']++;
            }

        }

        return $sentiment_counter;

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
            //echo $result['score']."<br>";
        }

        //echo $sentiment_counter['positive']."<br>";
        //echo $sentiment_counter['negative']."<br>";
        //echo $sentiment_counter['neutral'];


        //return $return_result;

        return $sentiment_counter;
    }




}
class GeolocationException extends \Exception {}