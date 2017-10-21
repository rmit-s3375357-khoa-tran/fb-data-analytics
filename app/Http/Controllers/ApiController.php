<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use ErrorException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    /**
     * empty results directory when
     * 1. reset the page
     * 2. finish analysing
     * to prevent too many unused csv results saved in server
     */
    public function emptyResultsDirectory()
    {
        $files = Storage::files('results');
        Storage::delete($files);
    }

    public function analyse($keyword, $stopwords)
    {
        // get type depending on if stopwords were set
        $type = ($stopwords == "null") ? 'raw' : 'processed';

        // get data for all resources
        $twitterData    = $this->getDataFromCsv($keyword, $type, 'twitter');
        $facebookData   = $this->getDataFromCsv($keyword, $type, 'facebook');
        $youtubeData    = $this->getDataFromCsv($keyword, $type, 'youtube');

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT NOTE
        |--------------------------------------------------------------------------
        |
        | The above data might be null when it was not collected in the 1st place,
        | so please check it before using them ( if($twitterData) will return true
        | when it is not null).
        |
        | In the end, return a single result page with whatever data needed to
        | display the page Nancy showed us the other day.
        |
        | OH YEAH we are almost there!!!
        |
        |                                               Grace
        |
        */

        list($results, $sentiments, $posCoordinates, $negCoordinates, $neuCoordinates) = $this->sentimentAnalysis($twitterData);
        //list($YTresults, $YTsentiments,$YTposCoordinates, $YTnegCoordinates, $YTneuCoordinates) = $this->YTsentimentAnalysis($youtubeData);
        return view ('pages.result', compact('keyword',
            'results', 'sentiments', 'posCoordinates','negCoordinates','neuCoordinates'));
    }

    protected function saveToCsvFile($results, $filename, $header)
    {
        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);

        foreach($results as $result)
            fputcsv($fp, $result);

        fclose($fp);
    }

    protected function preprocess($results, $stopwords)
    {
        $processedData = [];

        foreach ($results as $result)
        {
            $hasStopWord = false;

            foreach ($stopwords as $stopword)
            {
                if(strpos(strtolower($result['text']), strtolower($stopword)) !== false)
                    $hasStopWord = true;
            }

            if(! $hasStopWord )
                $processedData[] = $result;
        }

        return $processedData;
    }

    private function getDataFromCsv($keyword, $type, $source)
    {
        // parse the filename and open file
        $filename = 'results/' . $source . '_' . $keyword . '_' . $type . '.csv';
        try{
            $file = fopen($filename, 'r');
        } catch(ErrorException $e) {
            return null;
        }

        // get header for field name later
        if($result = fgetcsv($file))
            $header = $result;

        // put each line into array
        $data = [];
        while($result = fgetcsv($file))
        {
            foreach($result as $index => $value)
                $field[$header[$index]] = $value;

            $data[] = $field;
        }

        return $data;
    }

    // ##### DatumboxAPI sentiment analysis #####
    private function analyseTweet($results)
    {
//        require_once('DatumboxAPI.php');
        $DatumboxAPI = new DatumboxAPI(env('DATUM_BOX_API2'));
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        //$location = array();
        $positiveLocation = array();
        $negativeLocation = array();
        $neutralLocation = array();

        //echo "<br> DATUMBOX <br>";
        foreach ($results as $index => $result) {
            $message = $DatumboxAPI->TwitterSentimentAnalysis(str_replace('@', "", $result['tweet']));
            //echo "id: " . ($index + 1) . " value: " . $message . "<br>";

            if ($message == "negative") {
                $sentiment_counter['negative']++;
            } elseif ($message == "positive") {
                $sentiment_counter['positive']++;
            } else {
                $sentiment_counter['neutral']++;
            }


            // Get lat and long by address
            $address = $result['user_timezone']; // Google HQ
            if ($address != null) {
                $prepAddr = str_replace(' ', '+', $address);
                $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
                $output = json_decode($geocode);
                $latitude = $output->results[0]->geometry->location->lat;
                $longitude = $output->results[0]->geometry->location->lng;
                //array_push($location,array("lat"=>$latitude,"lng"=>$longitude));
                if ($message == "negative") {
                    array_push($negativeLocation, array("lat" => $latitude, "lng" => $longitude));
                } elseif ($message == "positive") {
                    array_push($positiveLocation, array("lat" => $latitude, "lng" => $longitude));
                } else {
                    array_push($neutralLocation, array("lat" => $latitude, "lng" => $longitude));
                }
            }
        }

        return [$sentiment_counter, $negativeLocation,$positiveLocation,$neutralLocation];

    }

    // ##### AzureAPI sentiment analysis #####
    private function sentimentAnalysis($results)
    {
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        $positiveLocation = array();
        $negativeLocation = array();
        $neutralLocation = array();

        $return = $this->azureSendRequest($results);

        foreach ($return['documents'] as $result) {
            $score = $result['score'];

            $this->incrementSentiment($result['score'], $sentiment_counter, $result);

            // Get lat and long by address
            $index = $result['id'] - 1;
            if ($results[$index]['place_longitude'] != null and $results[$index]['place_latitude'] != null)
            {
                $latitude =  (float) $results[$index]['place_latitude'];
                $longitude = (float) $results[$index]['place_longitude'];
                if ($score < 0.5) {
                    array_push($negativeLocation, array("lat" => $latitude, "lng" => $longitude));
                } elseif ($score > 0.5 == "positive") {
                    array_push($positiveLocation, array("lat" => $latitude, "lng" => $longitude));
                } else {
                    array_push($neutralLocation, array("lat" => $latitude, "lng" => $longitude));
                }
            }
            else{
                $this->getLonLat($results[$index]['user_timezone'],
                    $score,
                    $positiveLocation,
                    $negativeLocation,
                    $neutralLocation);
            }

        }
        return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
    }

    private function YTsentimentAnalysis($results)
    {
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        $positiveLocation = array();
        $negativeLocation = array();
        $neutralLocation = array();

        $return = $this->azureSendRequest($results);
        foreach ($return['documents'] as $result) {

            //Incrementing sentiment counter
            $this->incrementSentiment($result['score'], $sentiment_counter, $result);

            // Get lat and long by address
            $address = $this->getGeo($results[$result['id'] - 1]['author_channel_id']);
            $this->getLonLat($address,
                $result['score'],
                $positiveLocation,
                $negativeLocation,
                $neutralLocation);

        }
        return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
    }

    private function getLonLat($address, $score, &$positiveLocation, &$negativeLocation, &$neutralLocation)
    {
        if ($address != null) {
            $prepAddr = str_replace(' ', '+', $address);
            $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
            $output = json_decode($geocode);
            if ($output->results != null)
            {
                $latitude = $output->results{0}->geometry->location->lat;
                $longitude =$output->results{0}->geometry->location->lng;
                if ($score < 0.5) {
                    array_push($negativeLocation, array("lat" => $latitude, "lng" => $longitude));
                } elseif ($score > 0.5 == "positive") {
                    array_push($positiveLocation, array("lat" => $latitude, "lng" => $longitude));
                } else {
                    array_push($neutralLocation, array("lat" => $latitude, "lng" => $longitude));
                }
            }
        }
    }

    private function incrementSentiment($score, &$sentiment_counter, &$result)
    {
        if ($score < 0.5) {
            $sentiment_counter['negative']++;
            $results[$result['id'] - 1]['sentiment'] = 'negative';
        } elseif ($score > 0.5) {
            $sentiment_counter['positive']++;
            $results[$result['id'] - 1]['sentiment'] = 'positive';
        } else {
            $sentiment_counter['neutral']++;
            $results[$result['id'] - 1]['sentiment'] = 'neutral';
        }
    }

    private function azureSendRequest(&$results)
    {
        /* Converting messages into json body for request*/
        $body_message = '{ "documents" : [';
        foreach ($results as $index => $result) {
            $message = $result['text'];
            $message = str_replace('"', "'", $message);
            $id = $index + 1;
            $body_message .= '{ "language": "en", 
                                "id": ' . $id . ',
                                "text": "' . $message . '"},';
        }
        $body_message .= "]}";

        /*Creating a request*/
        $client = new Client(); //GuzzleHttp\Client
        $res = $client->request('POST', 'https://westcentralus.api.cognitive.microsoft.com/text/analytics/v2.0/sentiment', [
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
        $return = json_decode($json, true);

        return $return;
    }

    private function getGeo($authorChannelId)
    {
        $endpoint = "https://www.googleapis.com/youtube/v3/channels?".
            "key=" . env('YOUTUBE_API') .
            "&part=id,contentDetails,statistics,snippet".
            "&id=" . $authorChannelId;

        // get response from api and only continue when successful
        $response = $this->sendRequest($endpoint);
        if($response['success'] && isset($response['result'][0]))
        {
            // prepare result when successful response
            $items = $response['result'];
            foreach($items as $item)
            {
                if (isset($item->snippet->country))
                {
                    print_r($item->snippet->country);
                    echo("Country " . $item->snippet->country . " <br> ");
                    $country = $item->snippet->country;
                    return $country;
                }
            }
        }

        return null;
    }

    private function sendRequest($endpoint)
    {
        $client = new Client();
        try {
            $apiResponse = $client->get($endpoint);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        // in case response failed
        if ($apiResponse->getStatusCode() != 200)
            return [
                'success' => false,
                'message' => 'Response failed.'
            ];

        // in case of empty response
        $response = json_decode( (string)$apiResponse->getBody() );
        if (! $response)
            return [
                'success' => false,
                'message' => 'Empty response.'
            ];

        // return all items from response
        return [
            'success' => true,
            'result' => $response->items
        ];
    }
}
