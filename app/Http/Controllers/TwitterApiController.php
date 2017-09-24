<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class TwitterApiController extends ApiController
{
    public function collect(Request $request)
    {
        // keyword has to be set
        if(! isset($request->keyword))
            return json_encode([
                'success' => false,
                'message' => 'Keyword is required.'
            ]);

        // extract useful data from request
        $keyword = $request->keyword;
        $count = isset($request->count) && $request->count > 0 ? $request->count : 100;
        $stopwords = isset($request->stopwords) ? $request->stopwords : '';

        // tokenise stop words into array when it's set
        if($stopwords)
            $stopwords = explode(',', $stopwords);

        // execute python script using process, and save results to json file
        $process = new Process('python3 tweepy/tweepyStream.py '.$keyword.' '.$count.' > results/twitterStream.json');
        $process->run();

        // extract result from file
        $file = file_get_contents('results/twitterStream.json');
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

        // save both raw data and processed data into csv, pre-process with stop word
        if($results)
        {
            // header for csv
            $header = [
                "created_at", "tweet", "user_location", "user_timezone", "geo", "place_coordinates"
            ];
            $filename = "raw_data_for_".$keyword.".csv";

            $this->saveToCsvFile($results, $filename, $header);
            $response = [
                'success' => true,
                'path' => asset('results/'.$filename)
            ];

            if($stopwords)
            {
                $results = $this->preprocess($results, $stopwords);
                $filename = "preprocessed_data_for_".$keyword.".csv";

                $this->saveToCsvFile($results, $filename, $header);
                $response['path'] = asset('results/'.$filename);
            }

            return json_encode($response);
        }
        else
            return json_encode([
                'success' => false,
                'message' => 'Streaming failed.'
            ]);

//        //print_r($results);
//
//        /*****Analysing sentiments******/
//        //$sentiments = $this->sentimentAnalysis($results);
//        //list($tweetSentiments, $coordinates) = $this->analyseTweet($results);
//        list($sentiments,$posCoordinates,$negCoordinates,$neuCoordinates) = $this->sentimentAnalysis($results);
//        //list($sentiments,$posCoordinates,$negCoordinates,$neuCoordinates)=$this->analyseTweet($results);
//
//        //return view('twitter', compact('keyword', 'results', 'sentiments', 'tweetSentiments','coordinates'));
//        return view ('pages.twitter', compact('keyword', 'results', 'sentiments', 'posCoordinates','negCoordinates','neuCoordinates'));
    }

    private function extractUsefulFields($result)
    {
        $fields = null;

        // only extract info needed
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
}
