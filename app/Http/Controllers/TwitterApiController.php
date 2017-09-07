<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class TwitterApiController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        // extract useful data from request
        $keyword = isset($request->keyword) ? $request->keyword : 'twitterapi';
        $count = isset($request->count) && $request->count > 0 ? $request->count : 10;
        $stopwords = isset($request->stopwords) ? $request->stopwords : '';

        // tokenise stop words into array when it's set
        if($stopwords)
            $stopwords = explode(',', $stopwords);

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Only uncomment the following if python script is running on your local,
        | otherwise it will overwrites the test result file to empty file again.
        |
        */
        // execute python script using process, and save results to json file
//        $process = new Process('python3 tweepyStream.py '.$keyword.' '. $count .'> twitterStream.json');
//        $process->run();

        // extract result from file
        $file = file_get_contents('twitterStream.json');
        $results = [];

        // save objects into array by tokenising file read
        $tweetJson = strtok($file, "\r\n\n");
        while($tweetJson)
        {
            // try to extract useful fields from each result and save to array if exists
            $result = $this->extractUsefulFields(json_decode($tweetJson));
            if($result)
                $results[] = $result;

            // move onto next one
            $tweetJson = strtok("\r\n\n");
        }

        // save both raw data and processed data into csv, preprocess with stop word
        if($results)
        {
            $this->saveToCsvFile($results, "raw_data_for_".$keyword.".csv");

            if($stopwords)
            {
                $results = $this->preprocess($results, $stopwords);
                $this->saveToCsvFile($results, "preprocessed_data_for_".$keyword.".csv");
            }
        }

        //print_r($results);

        /*****Analysing sentiments******/
        //$sentiments = $this->sentimentAnalysis($results);
        //list($tweetSentiments, $coordinates) = $this->analyseTweet($results);
        list($sentiments,$posCoordinates,$negCoordinates,$neuCoordinates)=$this->analyseTweet($results);

        //return view('twitter', compact('keyword', 'results', 'sentiments', 'tweetSentiments','coordinates'));
        return view ('twitter', compact('keyword', 'results', 'sentiments', 'posCoordinates','negCoordinates','neuCoordinates'));
    }


// ##### DatumboxAPI sentiment analysis #####
    private function analyseTweet($results)
    {
        require_once('DatumboxAPI.php');
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
            if (array_key_exists("user_timezone", $result)) {
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
        }

        return [$sentiment_counter, $negativeLocation,$positiveLocation,$neutralLocation];

    }
// ##### AzureAPI sentiment analysis #####
    private function sentimentAnalysis($results)
    {

        /* Converting messages into json body for request*/
        $body_message = '{ "documents" : [';
        foreach ($results as $index => $result) {
            $message = $result['tweet'];
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
                'Ocp-Apim-Subscription-Key' => env('AZURE_KEY_2')
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

        //echo "AZURE" . "<br>";
        foreach ($results['documents'] as $result) {
            $score = $result['score'];
            $id = $result['id'];

            if ($score < 0.5) {
                $sentiment_counter['negative']++;
                //echo "id: " . ($id) . " value: negative<br>";
            } elseif ($score > 0.5) {
                $sentiment_counter['positive']++;
                //echo "id: " . ($id) . " value: positive<br>";
            } else {
                $sentiment_counter['neutral']++;
                //echo "id: " . ($id) . " value: neutral<br>";
            }
        }

        return $sentiment_counter;
    }

    private function saveToCsvFile($results, $filename)
    {
        $fp = fopen($filename, 'w');
        // header for csv
        $header = [
            "created_at", "tweet", "user_location", "user_timezone", "geo", "place_coordinates"
        ];

        fputcsv($fp, $header);

        foreach($results as $result)
            fputcsv($fp, $result);

        fclose($fp);
    }

    private function extractUsefulFields($result)
    {
        $fields = null;

        // only extra info neededz
        if( isset($result->created_at) )
            $fields = [
                'created_at'        => $result->created_at,
                'tweet'             => $result->text,
                'user_location'     => $result->user->location,
                'user_timezone'     => $result->user->time_zone,
                'geo'               => $result->geo,
                'place_longitude'   => isset($result->place) ? $result->place->bounding_box->coordinates[0][0][0] : null,
                'place_latitude'    => isset($result->place) ? $result->place->bounding_box->coordinates[0][0][1] : null,
            ];

        return $fields;
    }

    private function preprocess($results, $stopwords)
    {
        $processedData = [];

        foreach ($results as $result)
        {
            $hasStopWord = false;

            foreach ($stopwords as $stopword)
            {
                if(strpos(strtolower($result['tweet']), strtolower($stopword)) !== false)
                    $hasStopWord = true;
            }

            if(! $hasStopWord )
                $processedData[] = $result;
        }

        return $processedData;
    }
}
