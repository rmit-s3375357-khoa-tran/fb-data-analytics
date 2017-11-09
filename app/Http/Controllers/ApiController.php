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

        return redirect(route('home'));
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
        list($FBresults, $FBsentiments) = $this->FBsentimentAnalysis($facebookData);

        if ($results == false){
            $azure_errors = "Azure keys are invalid. Please enter new API keys.";
            return view('pages.azure', compact('azure_errors'));
        }
        $posCoordinates = array_merge($TposCoordinates, $YTposCoordinates);
        $negCoordinates = array_merge($TnegCoordinates, $YTnegCoordinates);
        $neuCoordinates = array_merge($TneuCoordinates, $YTneuCoordinates);

        // remove files after analysing
        //$this->emptyResultsDirectory();

        return view('pages.result', compact('keyword',
            'results', 'sentiments', 'posCoordinates', 'negCoordinates', 'neuCoordinates',
            'YTsentiments', 'YTresults', 'FBsentiments', 'FBresults'));
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
        $positiveLocation = $negativeLocation = $neutralLocation= array();

        if ($results == null) {
            return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
        }

        //$messages = $DatumboxAPI->multiRequest(0, $results);
//        if ($messages == false) {
//            return $this->sentimentAnalysis($results);
//        }
        foreach ($results as $index => $result) {
            $score = null;
            $message = $DatumboxAPI->TwitterSentimentAnalysis(str_replace('@', "", $result['text']));

            //incrementing sentiment counter
//            $message = null;
//            if(isset($messages[$index])){
//                $message = $messages[$index];
//            }
            if ($message == "negative") {
                $score = 0;
                $sentiment_counter['negative']++;
                $results[$index]['sentiment'] = 'negative';
            } elseif ($message == "positive") {
                $sentiment_counter['positive']++;
                $results[$index]['sentiment'] = 'positive';
                $score = 1;
            } elseif ($message == "neutral") {
                $sentiment_counter['neutral']++;
                $results[$index]['sentiment'] = 'neutral';
                $score = 0.5;
            }

            // Get lat and long by address
            if (isset($result['place_longitude']) and isset($result['place_latitude'])) {
                $latitude = (float)$result['place_latitude'];
                $longitude = (float)$result['place_longitude'];
                $results[$index]['location'] = $latitude.", ".$longitude;

                $this->addLocation(array("lat" => $latitude, "lng" => $longitude),
                    $score, $positiveLocation, $negativeLocation, $neutralLocation);
            } elseif ($results[$index]['user_timezone'] || $results[$index]['user_location']) {
                $results[$index]['location'] = ($results[$index]['user_timezone']? $results[$index]['user_timezone']
                    : $results[$index]['user_location']);
                $this->getLonLat($result['user_timezone'],
                    $score,
                    $positiveLocation,
                    $negativeLocation,
                    $neutralLocation, $results);
            }
        }

        return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
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
        $neutralLocation= array();

        if ($results == null) {
            return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
        }

        $return = $this->azureSendRequest($results);
        if ($return == false){
            return false;
        }

        foreach ($return['documents'] as $result) {
            $score = $result['score'];

            $this->incrementSentiment($result['score'], $sentiment_counter, $result, $results);

            // Get lat and long by address
            $index = $result['id'] - 1;
            if (isset($results[$index]['place_longitude']) and isset($results[$index]['place_latitude'])) {
                $latitude = (float)$results[$index]['place_latitude'];
                $longitude = (float)$results[$index]['place_longitude'];
                $results[$index]['location'] = $latitude.", ".$longitude;

                $this->addLocation(array("lat" => $latitude, "lng" => $longitude),
                    $score, $positiveLocation, $negativeLocation, $neutralLocation);
            } elseif ($results[$index]['user_timezone'] || $results[$index]['user_location']) {
                $results[$index]['location'] = ($results[$index]['user_timezone']? $results[$index]['user_timezone']
                    : $results[$index]['user_location']);
                $this->getLonLat($results[$index]['location'],
                    $score,
                    $positiveLocation,
                    $negativeLocation,
                    $neutralLocation);
            }
        }
        return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
    }

    private function FBsentimentAnalysis($results)
    {
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        if ($results == null) {
            return [$results, $sentiment_counter];
        }
        //print_r($results);
        $return = $this->azureSendRequest($results);
        if ($return == false){
            return false;
        }
        foreach ($return['documents'] as $result) {
            //Incrementing sentiment counter
            $author = $results[$result['id'] - 1]['comment_author'];
            $results[$result['id'] - 1]['comment_author'] = str_replace(array("'", "`", '"'), "", $author);
            $results[$result['id'] - 1]['text'] = str_replace(array("\r\n", "\n", "\r", "'", "`", '"'), "", $results[$result['id'] - 1]['text']);
            $this->incrementSentiment($result['score'], $sentiment_counter, $result, $results);
        }
        return [$results, $sentiment_counter];
    }

    private function YTsentimentAnalysis($results)
    {
        $sentiment_counter = array("positive" => 0, "negative" => 0, "neutral" => 0);
        $positiveLocation = array();
        $negativeLocation = array();
        $neutralLocation= array();

        if ($results == null) {
            return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
        }

        $return = $this->azureSendRequest($results);
        if ($return == false){
            return false;
        }
        foreach ($return['documents'] as $result) {

            //Incrementing sentiment counter
            $this->incrementSentiment($result['score'], $sentiment_counter, $result, $results);

            // Get lat and long by address
            $address = $this->getGeo($results[$result['id'] - 1]['author_channel_id']);
            if ($address != null || $address != "undefined") {
                $results[$result['id'] - 1]['location'] = $address;
            }
            else {
                // 2. Analyse from text using Geotext
                $text = $results[$result['id'] - 1]['text'];
                $geoResult = shell_exec(' python '.public_path().'/geotext/geo.py "'.$text.'"');
                echo("Geo result location: " . $geoResult . "<br>");
            }
            $this->getLonLat($address,
                $result['score'],
                $positiveLocation,
                $negativeLocation,
                $neutralLocation, $results);

        }
        return [$results, $sentiment_counter, $negativeLocation, $positiveLocation, $neutralLocation];
    }

    private function getLonLat($address, $score, &$positiveLocation, &$negativeLocation, &$neutralLocation)
    {
        if ($address != null) {
            $prepAddr = str_replace(' ', '+', $address);
            $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
            $output = json_decode($geocode);
            if ($output != null) {
                if ($output->results != null){
                    $latitude = $output->results{0}->geometry->location->lat;
                    $longitude = $output->results{0}->geometry->location->lng;
                    $this->addLocation(array("lat" => $latitude, "lng" => $longitude),
                        $score, $positiveLocation, $negativeLocation, $neutralLocation);
                }
            }
        }
    }

    private function addLocation(
        $coord,
        $score,
        &$positiveLocation,
        &$negativeLocation,
        &$neutralLocation
    ) {
        if ($score < 0.5) {
            array_push($negativeLocation, $coord);
        } elseif ($score > 0.5) {
            array_push($positiveLocation, $coord);
        } elseif ($score == 0.5) {
            array_push($neutralLocation, $coord);
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
        } elseif ($score == 0.5) {
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
        $res = null;
        try {
            $res = $client->request('POST',
                'https://westcentralus.api.cognitive.microsoft.com/text/analytics/v2.0/sentiment', [
                    'headers' => [
                        'content-type' => 'application/json',
                        'Ocp-Apim-Subscription-Key' => $key->key
                    ],
                    'body' => $body_message
                ]);
        } catch (\Exception $e) {
            // How can I get the response body?
            return false;
        }

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
        $endpoint = "https://www.googleapis.com/youtube/v3/channels?" .
            "key=" . config('setting.youtube.key') .
            "&part=id,contentDetails,statistics,snippet" .
            "&id=" . $authorChannelId;

        // get response from api and only continue when successful
        $response = $this->sendRequest($endpoint);
        if ($response['success'] && isset($response['result'][0])) {
            // prepare result when successful response
            $items = $response['result'];
            foreach ($items as $item) {
                if (isset($item->snippet->country)) {
                    return $item->snippet->country;
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
        if ($apiResponse->getStatusCode() != 200) {
            return [
                'success' => false,
                'message' => 'Response failed.'
            ];
        }

        // in case of empty response
        $response = json_decode((string)$apiResponse->getBody());
        if (!$response) {
            return [
                'success' => false,
                'message' => 'Empty response.'
            ];
        }

        // return all items from response
        return [
            'success' => true,
            'result' => $response->items
        ];
    }
}
