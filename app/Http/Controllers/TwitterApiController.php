<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Abraham\TwitterOAuth\TwitterOAuth;

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
        // extract useful data from request
        $keyword = isset($request->keyword) ? $request->keyword : 'twitterapi';
        $count = isset($request->count) && $request->count > 0 ? $request->count : 100;
        $stopwords = isset($request->stopwords) ? $request->stopwords : '';

        // tokenise stop words into array when it's set
        if($stopwords)
            $stopwords = explode(',', $stopwords);

        // execute python script using process, and extract output to array
//        $process = new Process('python3 tweepyStream.py '.$keyword.' '. $count .'> twitterStream.txt');
//        $process->run();

        $file = file_get_contents('twitterStream.json');
        $results = [];

        $json_tweet = strtok($file, "\r\n\n");
        while($json_tweet)
        {
            $results[] = json_decode($json_tweet);

            $json_tweet = strtok("\r\n\n");
        }

        // save both raw data and processed data into csv, preprocess with stop word
        if($results)
        {
            $this->saveToCsvFile($results, "raw_data_for_".$keyword.".csv");
            #$results = $this->preprocess($results, $stopwords);
            $this->saveToCsvFile($results, "preprocessed_data_for_".$keyword.".csv");
        }

        return view('twitter', compact('keyword', 'results'));
    }

    private function saveToCsvFile($results, $filename)
    {
        $fp = fopen($filename, 'w');
        // header for csv
        $header = [
            "created_at", "tweet", "user_location", "user_timezone", "geo", "place_coordinates"
        ];

        fputcsv($fp, $header);
        
        foreach ($results as $result)
        {
            $fields = $this->prepareFieldsForCsv($result);
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

    private function prepareFieldsForCsv($result)
    {
        // only extra info needed
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

    private function preprocess($results, $stopword)
    {
        $processedData = [];

        foreach ($results as $result)
            if(strpos(strtolower($result->text), strtolower($stopword)) === false)
                $processedData[] = $result;

        return $processedData;
    }

}
