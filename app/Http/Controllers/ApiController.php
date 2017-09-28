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

        return view('pages.result', compact($results));
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
        $positiveLocation = array();
        $negativeLocation = array();
        $neutralLocation = array();

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
        $return = json_decode($json, true);
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);

        //echo "AZURE" . "<br>";
        foreach ($return['documents'] as $result) {
            $score = $result['score'];

            if ($score < 0.5) {
                $sentiment_counter['negative']++;
            } elseif ($score > 0.5) {
                $sentiment_counter['positive']++;
            } else {
                $sentiment_counter['neutral']++;
            }

            // Get lat and long by address
            $index = $result['id'] - 1;
            $address = $results[$index]['user_timezone']; // Google HQ
            if ($address != null) {
                $prepAddr = str_replace(' ', '+', $address);
                $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
                $output = json_decode($geocode);
                $latitude = $output->results[0]->geometry->location->lat;
                $longitude = $output->results[0]->geometry->location->lng;
                //array_push($location,array("lat"=>$latitude,"lng"=>$longitude));
                if ($score < 0.5) {
                    array_push($negativeLocation, array("lat" => $latitude, "lng" => $longitude));
                } elseif ($score > 0.5 == "positive") {
                    array_push($positiveLocation, array("lat" => $latitude, "lng" => $longitude));
                } else {
                    array_push($neutralLocation, array("lat" => $latitude, "lng" => $longitude));
                }
            }

        }

        return [$sentiment_counter, $negativeLocation,$positiveLocation,$neutralLocation];

    }
}
