<?php

namespace App\Http\Controllers;

use App\AzureApiKey;
use App\Http\Requests\Request;
use Carbon\Carbon;
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
        $type = ($stopwords == "null") ? '_raw' : '_processed';

        // get data for all resources
        $twitterData = $this->getDataFromCsv($keyword, $type, 'twitter');
        $facebookData = $this->getDataFromCsv($keyword, '', 'facebook');
        $youtubeData = $this->getDataFromCsv($keyword, $type, 'youtube');

        // analyse data
        list($results, $sentiments, $TnegCoordinates, $TposCoordinates, $TneuCoordinates) = $this->sentimentAnalysis($twitterData);
        list($YTresults, $YTsentiments, $YTnegCoordinates, $YTposCoordinates, $YTneuCoordinates) = $this->YTsentimentAnalysis($youtubeData);
        $posCoordinates = array_merge($TposCoordinates, $YTposCoordinates);
        $negCoordinates = array_merge($TnegCoordinates, $YTnegCoordinates);
        $neuCoordinates = array_merge($TneuCoordinates, $YTneuCoordinates);

        // remove files after analysing
        $this->emptyResultsDirectory();

        return view('pages.result', compact('keyword',
            'results', 'sentiments', 'posCoordinates', 'negCoordinates', 'neuCoordinates',
            'YTsentiments', 'YTresults'));
    }

    protected function save($request, $results, $header, $origin)
    {
        $filename = 'results/' . $origin . '_' . $request->keyword . "_raw.csv";
        $this->saveToCsvFile($results, $filename, $header);
        $response = [
            'success' => true,
            'path' => asset($filename)
        ];

        if ($request->stopwords != "" || $request->date != "") {
            $results = $this->preprocess($results, $request);
            $filename = 'results/' . $origin . '_' . $request->keyword . "_processed.csv";
            $this->saveToCsvFile($results, $filename, $header);
            $response['path'] = asset($filename);
        }

        return json_encode($response);
    }

    protected function saveToCsvFile($results, $filename, $header)
    {
        $fp = fopen($filename, 'w');
        fputcsv($fp, $header);

        foreach ($results as $result) {
            try {
                fputcsv($fp, $result);
            } catch (\Exception $e) {

            }
        }

        fclose($fp);
    }

    protected function preprocess($results, $request)
    {
        // process date limit
        $startingDate = $request->date != "" ?
            Carbon::parse($request->date) :
            Carbon::today()->subMonth();

        // placeholder for result
        $processedData = [];
        foreach ($results as $result) {
            if ($result['created_at'] >= $startingDate) {
                $processedData[] = $result;
            }
        }

        // process stop words
        if ($request->stopwords != "") {
            $processedData = $this->processStopWords($processedData, $request->stopwords);
        }

        return $processedData;
    }

    private function processStopWords($results, $stopwordString)
    {
        $stopwords = explode(',', $stopwordString);

        // placeholder for result
        $processedData = [];

        foreach ($results as $result) {
            $hasStopWord = false;

            foreach ($stopwords as $stopword) {
                if (strpos(strtolower($result['text']), strtolower($stopword)) !== false) {
                    $hasStopWord = true;
                }
            }

            if (!$hasStopWord) {
                $processedData[] = $result;
            }
        }

        return $processedData;
    }

    private function getDataFromCsv($keyword, $type, $source)
    {
        // parse the filename and open file
        $filename = 'results/' . $source . '_' . $keyword . $type . '.csv';
        try {
            $file = fopen($filename, 'r');
        } catch (ErrorException $e) {
            return null;
        }

        // get header for field name later
        if ($result = fgetcsv($file)) {
            $header = $result;
        }

        // put each line into array
        $data = [];
        while ($result = fgetcsv($file)) {
            foreach ($result as $index => $value) {
                $field[$header[$index]] = $value;
            }

            $data[] = $field;
        }

        return $data;
    }

    /**
     * DatumboxAPI sentiment analysis
     *
     * @param $results
     * @return array
     */
    private function analyseTweet($results)
    {
        $DatumboxAPI = new DatumboxAPI(config('setting.datum_box.key'));
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        $positiveLocation = array();
        $negativeLocation = array();
        $neutralLocation = array();

        foreach ($results as $index => $result) {
            $message = $DatumboxAPI->TwitterSentimentAnalysis(str_replace('@', "", $result['tweet']));

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

                if ($message == "negative") {
                    array_push($negativeLocation, array("lat" => $latitude, "lng" => $longitude));
                } elseif ($message == "positive") {
                    array_push($positiveLocation, array("lat" => $latitude, "lng" => $longitude));
                } else {
                    array_push($neutralLocation, array("lat" => $latitude, "lng" => $longitude));
                }
            }
        }

        return [$sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
    }

    /**
     * AzureAPI sentiment analysis
     *
     * @param $results
     * @return array
     */
    private function sentimentAnalysis($results)
    {
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        $positiveLocation = array();
        $negativeLocation = array();
        $neutralLocation = array();
        if ($results == null) {
            return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
        }

        $return = $this->azureSendRequest($results);

        foreach ($return['documents'] as $result) {
            $score = $result['score'];

            $this->incrementSentiment($result['score'], $sentiment_counter, $result, $results);

            // Get lat and long by address
            $index = $result['id'] - 1;
            $results[$index]['user_location'] = str_replace(
                array("\r\n", "\n", "\r", "'", "`", '"'),
                "", $results[$index]['user_location']);
            if (isset($results[$index]['place_longitude']) and isset($results[$index]['place_latitude'])) {
                $latitude = (float)$results[$index]['place_latitude'];
                $longitude = (float)$results[$index]['place_longitude'];
                $results[$index]['location'] = $results[$index]['user_location'];

                $this->addLocation($latitude, $longitude,
                    $result, $positiveLocation, $negativeLocation, $neutralLocation);
            } else {
                $results[$index]['location'] = $results[$index]['user_timezone'];
                $this->getLonLat($results[$index]['location'],
                    $result,
                    $positiveLocation,
                    $negativeLocation,
                    $neutralLocation, $results);
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
        if ($results == null) {
            return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
        }

        $return = $this->azureSendRequest($results);
        foreach ($return['documents'] as $result) {

            //Incrementing sentiment counter
            $this->incrementSentiment($result['score'], $sentiment_counter, $result, $results);
            $this->getLonLat($results[$result['id'] - 1]['location'],
                $result,
                $positiveLocation,
                $negativeLocation,
                $neutralLocation);

        }
        return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
    }

    private function getLonLat($address, $result, &$positiveLocation, &$negativeLocation, &$neutralLocation)
    {
        if ($address != null) {
            $prepAddr = str_replace(' ', '+', $address);
            $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
            $output = json_decode($geocode);
            if ($output->results != null) {
                $latitude = $output->results{0}->geometry->location->lat;
                $longitude = $output->results{0}->geometry->location->lng;
                $this->addLocation($latitude, $longitude,
                    $result, $positiveLocation, $negativeLocation, $neutralLocation);
            }
        }
    }

    private function addLocation(
        $latitude,
        $longitude,
        $result,
        &$positiveLocation,
        &$negativeLocation,
        &$neutralLocation
    ) {
        $score = $result['score'];
        if ($score < 0.5) {
            array_push($negativeLocation, array("lat" => $latitude, "lng" => $longitude));
        } elseif ($score > 0.5) {
            array_push($positiveLocation, array("lat" => $latitude, "lng" => $longitude));
        } elseif ($score == 0.5) {
            array_push($neutralLocation, array("lat" => $latitude, "lng" => $longitude));
        }
    }

    private function incrementSentiment($score, &$sentiment_counter, &$result, &$results)
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

        // Azure API key from DB
        $key = AzureApiKey::where('name', 'key1')->first();

        /*Creating a request*/
        $client = new Client(); //GuzzleHttp\Client
        $res = $client->request('POST',
            'https://westcentralus.api.cognitive.microsoft.com/text/analytics/v2.0/sentiment', [
                'headers' => [
                    'content-type' => 'application/json',
                    'Ocp-Apim-Subscription-Key' => $key->key
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
}
