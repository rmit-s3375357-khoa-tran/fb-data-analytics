<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\vendor;
use App\Http\Requests;

class YoutubeApiController extends ApiController
{
    public function search(Request $request)
    {
        $keyword = isset($request->keyword) ? $request->keyword : 'youtubeapi';
        $results = $this->ytcomment($keyword);
        print_r($results);
        return view('youtube', compact('keyword', 'results'));

    }

    public function ytcomment($keyword)
    {
        //require_once('C:\xampp\htdocs\fb-data-analytics\vendor\google\apiclient\src\Google\Client.php');
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
        set_time_limit(60 * 3);

        $youtubeAPIKey = env('YOUTUBE_API');
        $videos = array();


        /***** Getting top 3 youtube videos for keyword ****/
        /* make api request */
        $url = "https://www.googleapis.com/youtube/v3/search?key=" . $youtubeAPIKey .
            "&part=id,snippet&q=" . $keyword . "&maxResults=" . "3" . "&type=video";


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'YouTube API Tester',
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST=> 0,
            CURLOPT_CAINFO => "C:/xampp/php/cacert.pem",
            CURLOPT_CAPATH => "C:/xampp/php/cacert.pem",
            CURLOPT_FOLLOWLOCATION => TRUE
        ));
        $resp = curl_exec($curl);

        curl_close($curl);

        if ($resp) {
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

            echo("<hr>");
            echo("<hr>");

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_USERAGENT => 'YouTube API Tester',
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_CAINFO => "C:/xampp/php/cacert.pem",
                CURLOPT_CAPATH => "C:/xampp/php/cacert.pem",
                CURLOPT_FOLLOWLOCATION => TRUE
            ));
            $resp = curl_exec($curl);

            curl_close($curl);

            if ($resp) {
                $json = json_decode($resp);

                if ($json) {
                    echo("JSON decoded<br>");

                    $items = $json->items;

                    //test for no comments
                    foreach ($items as $item) {
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
                        echo ("Commenter channel id: ".  $authorChannelId );

                        // Make a new request to get the channel location
                        echo ("Commenter channel url: ". $authorChannelUrl);
                        echo("On " . $publishedOn . " , Replies :" . $replyCount);
                        echo("<hr>");
                    }


                } else
                    exit("Error. could not parse JSON." . json_last_error_msg());


            } // if resp
        }
    }

}
