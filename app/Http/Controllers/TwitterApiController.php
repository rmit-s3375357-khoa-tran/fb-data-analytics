<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class TwitterApiController extends ApiController
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
        $process = new Process('python3 tweepy/tweepyStream.py '.$keyword.' '.$count.'> twitterStream.json');
        $process->run();

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

        list($sentiments,$posCoordinates,$negCoordinates,$neuCoordinates) = $this->sentimentAnalysis($results);

        return view ('twitter', compact('keyword', 'results', 'sentiments', 'posCoordinates','negCoordinates','neuCoordinates'));
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
