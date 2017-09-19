<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class YoutubeApiController extends ApiController
{
    public function search(Request $request)
    {
        $keyword = isset($request->keyword) ? $request->keyword : 'youtubeapi';
        $results = $this->ytcomment($keyword);
        print_r($results);
        return view('youtube', compact('keyword', 'results'));
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
            $json = json_decode($resp);

            if ($json) {
                foreach ($json->items as $searchResult) {
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
