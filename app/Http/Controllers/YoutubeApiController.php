<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class YoutubeApiController extends ApiController
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('YOUTUBE_API');
    }

    public function search(Request $request)
    {
        // keyword has to be set
        if( $request->keyword == "")
            return json_encode([
                'success' => false,
                'message' => 'Keyword is required.'
            ]);

        // extract useful data from request
        $keyword = $request->keyword;
        $count = ($request->count != "" && $request->count > 0) ? $request->count : 3;

        // get data from api
        $endpoint = "https://www.googleapis.com/youtube/v3/search?" .
            "key=" . $this->apiKey .
            "&part=id,snippet" .
            "&q=" . $keyword .
            "&maxResults=" . $count*2 . // to collect twice results for filtering
            "&type=video";

        // get response from api and return if failed
        $response = $this->sendRequest($endpoint);
        if(! $response['success'])
            return json_encode($response);

        // prepare result when successful response
        $items = $response['result'];
        $videoDetails = [];
        foreach ($items as $item)
        {
            $publishedAt = Carbon::parse($item->snippet->publishedAt);
            $startingDate = $request->date != ""?
                Carbon::parse($request->date) :
                Carbon::today()->subWeek();

            // when it's published after starting date and not reach count yet
            if($publishedAt >= $startingDate && count($videoDetails) < $count)
                $videoDetails[] = [
                    'videoId'       => $item->id->videoId,
                    'title'         => $item->snippet->title,
                    'description'   => $item->snippet->description,
                    'publishedAt'   => $publishedAt->toDateTimeString()
                ];
        }

        return json_encode([
            'success' => true,
            'data' => $videoDetails
        ]);


//        $keyword = isset($request->keyword) ? $request->keyword : 'youtubeapi';
//        $results = $this->ytcomment($keyword);
//        print_r($results);
//        return view('pages.youtube', compact('keyword', 'results'));
    }

    public function addCustomUrls(Request $request)
    {
        // return false when urls not set
        if(! isset($request->urls) && $request->urls == "")
            return json_encode([
                'success' => false,
                'message' => 'URL(s) not found.'
            ]);

        // get video info from urls
        $items = [];
        $urls = explode(",", $request->urls);
        foreach($urls as $url)
        {
            // get the video id
            parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
            $id = $my_array_of_vars['v'];

            // prepare endpoint
            $endpoint = 'https://www.googleapis.com/youtube/v3/videos?'.
                "key=" . $this->apiKey .
                "&part=snippet" .
                '&id=' . $id;

            // get response from api and return if failed
            $response = $this->sendRequest($endpoint);
            if($response['success'] && isset($response['result'][0]))
                array_push($items, $response['result'][0]);
        }

        // when all urls are invalid
        if(! count($items))
            return json_encode([
                'success' => false,
                'message' => 'URL(s) not found.'
            ]);

        // get response from api and add to array only when successful
        $videoDetails = [];
        foreach ($items as $item)
        {
            $publishedAt = Carbon::parse($item->snippet->publishedAt);
            $videoDetails[] = [
                'videoId' => $item->id,
                'title' => $item->snippet->title,
                'description' => $item->snippet->description,
                'publishedAt' => $publishedAt->toDateTimeString()
            ];
        }

        return json_encode([
            'success'   => true,
            'data'      => $videoDetails
        ]);
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


    private function ytcomment($keyword)
    {
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
        set_time_limit(60 * 3);

        $youtubeAPIKey = env('YOUTUBE_API');
        $videos = array();

        /***** Getting top 3 youtube videos for keyword ****/
        /* make api request */
        $url = "https://www.googleapis.com/youtube/v3/search?key=" . $youtubeAPIKey .
            "&part=id,snippet&q=" . $keyword . "&maxResults=" . "3" . "&type=video";

        $client = new Client();
        try {
            $apiResponse = $client->get($url);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        if ($apiResponse->getStatusCode() == 200)
        {
            $resp = (string)$apiResponse->getBody();
            $results = json_decode($resp);

            if ($results) {
                foreach ($results->items as $searchResult) {
                    array_push($videos, $searchResult->id->videoId);
                }
            }
        }

        /*** Getting video comments for each video id ***/
        foreach ($videos as $videoId)
        {
            // make api request
            $url = "https://www.googleapis.com/youtube/v3/commentThreads?key=" . $youtubeAPIKey .
                "&part=id,snippet,replies&videoId=" . $videoId;

            $client = new Client();
            try {
                $apiResponse = $client->get($url);
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            if ($apiResponse->getStatusCode() == 200)
            {
                $resp = (string)$apiResponse->getBody();
                $json = json_decode($resp);

                if ($json)
                {
                    foreach ($json->items as $item) {
                        $id = $item->id;
                        $author = $item->snippet->topLevelComment->snippet->authorDisplayName;
                        $authorPic = $item->snippet->topLevelComment->snippet->authorProfileImageUrl;
                        $authorChannelUrl = $item->snippet->topLevelComment->snippet->authorChannelUrl;
                        $authorChannelId = $item->snippet->topLevelComment->snippet->authorChannelId->value;

                        $textDisplay = $item->snippet->topLevelComment->snippet->textDisplay;
                        $publishedOn = $item->snippet->topLevelComment->snippet->publishedAt;
                        $replyCount = $item->snippet->totalReplyCount;


                        echo("\"" . $textDisplay . "\"  by " . $author . "<br>");
                        echo("<img src=\"" . $authorPic . "\" border=0 align=left>");
                        echo("On " . $publishedOn . " , Replies :" . $replyCount);
                        echo("<hr>");
                    }
                }
                else
                    exit("Error. could not parse JSON." . json_last_error_msg());
            }
        }
    }
}
