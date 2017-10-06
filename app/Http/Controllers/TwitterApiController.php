<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class TwitterApiController extends ApiController
{
    public function collect(Request $request)
    {
        // keyword has to be set
        if($request->keyword == "")
            return json_encode([
                'success' => false,
                'message' => 'Keyword is required.'
            ]);

        // extract useful data from request
        $keyword    = $request->keyword;
        $count      = $request->count > 0 ? $request->count : 100;
        $stopwords  = $request->stopwords;

        // tokenise stop words into array when it's set
        if($stopwords)
            $stopwords = explode(',', $stopwords);

        // setup twitter oauth and client
        $twitter = new TwitterOAuth(
            config('setting.twitter_oauth.customer_key'),
            config('setting.twitter_oauth.customer_secret'),
            config('setting.twitter_oauth.access_token'),
            config('setting.twitter_oauth.access_token_secret')
        );
        $twitter->get("account/verify_credentials");

        // search keyword and counts
        $response = $twitter->get("search/tweets", [
                'q'     => $keyword,
                'lang'  => 'en',
                'count' => $count
            ])->statuses;

        // extract results from response
        $results = [];
        foreach ($response as $item)
        {
            // try to extract useful fields from each result and save to array if exists
            $result = $this->extractUsefulFields($item);
            if($result)
                $results[] = $result;
        }

        // save both raw data and processed data into csv, pre-process with stop word
        if(count($results))
            return $this->save($keyword, $stopwords, $results);
        else
            return json_encode([
                'success' => false,
                'message' => 'Streaming failed.'
            ]);
    }

    public function stream(Request $request)
    {
        // keyword has to be set
        if($request->keyword == "")
            return json_encode([
                'success' => false,
                'message' => 'Keyword is required.'
            ]);

        // extract useful data from request
        $keyword    = $request->keyword;
        $count      = $request->count > 0 ? $request->count : 100;
        $stopwords  = $request->stopwords;

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
        if(count($results))
            return $this->save($keyword, $stopwords, $results);
        else
            return json_encode([
                'success' => false,
                'message' => 'Streaming failed.'
            ]);
    }

    private function extractUsefulFields($result)
    {
        $fields = null;

        // only extract info needed
        if( isset($result->created_at) )
            $fields = [
                'created_at'        => $result->created_at,
                'text'              => $result->text,
                'user_location'     => $result->user->location,
                'user_timezone'     => $result->user->time_zone,
                'geo'               => $result->geo,
                'place_longitude'   => isset($result->place) ? $result->place->bounding_box->coordinates[0][0][0] : null,
                'place_latitude'    => isset($result->place) ? $result->place->bounding_box->coordinates[0][0][1] : null,
            ];

        return $fields;
    }

    private function save($keyword, $stopwords, $results)
    {
        // header for csv
        $header = [
            'created_at',
            'text',
            'user_location',
            'user_timezone',
            'geo',
            'place_longitude',
            'place_latitude',
        ];
        $filename = 'results/twitter_'.$keyword."_raw.csv";

        $this->saveToCsvFile($results, $filename, $header);
        $response = [
            'success' => true,
            'path' => asset($filename)
        ];

        if($stopwords != "")
        {
            $results = $this->preprocess($results, $stopwords);
            $filename = 'results/twitter_'.$keyword."_processed.csv";

            $this->saveToCsvFile($results, $filename, $header);
            $response['path'] = asset($filename);
        }

        return json_encode($response);
    }
}
