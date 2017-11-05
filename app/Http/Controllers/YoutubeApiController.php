<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\Process\Process;

class YoutubeApiController extends ApiController
{

    public function search(Request $request)
    {
        $keyword = isset($request->keyword) ? $request->keyword : 'youtubeapi';
        $results = $this->ytcomment1($keyword);
        print_r($results);
        return view('youtube', compact('keyword', 'results'));

    }

    public function ytcomment2($keyword)
    {

        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
        set_time_limit(60 * 3);

        $g_youtubeDataAPIKey = "AIzaSyA_sxi8v-wE-ZhLVZE7jCWdNUHfWjsnMHI";
        $videoId = "9rjnP5EVpQc";

        // make api request
        $url = "https://www.googleapis.com/youtube/v3/commentThreads?key=" . $g_youtubeDataAPIKey .
            "&part=id,snippet,replies&videoId=" . $videoId;


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'YouTube API Tester',
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CAINFO => "/Applications/XAMPP/cacerts.pem",
            CURLOPT_CAPATH => "/Applications/XAMPP/cacerts.pem",
            CURLOPT_FOLLOWLOCATION => TRUE
        ));
        $resp = curl_exec($curl);

        curl_close($curl);

        if ($resp) {
            $json = json_decode($resp);

            if ($json) {
                echo("JSON decoded<br>");

                $items = $json->items;

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
                    echo("On " . $publishedOn . " , Replies :" . $replyCount);
                    echo("<hr>");
                    echo("<br>");
                }


            } else
                exit("Error. could not parse JSON." . json_last_error_msg());


        } // if resp

    }

    // Geocode using youtube API
    public function ytcomment1($keyword)
    {
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
        set_time_limit(60 * 3);

        $youtubeAPIKey = "AIzaSyA_sxi8v-wE-ZhLVZE7jCWdNUHfWjsnMHI";
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
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CAINFO => "/Applications/XAMPP/cacerts.pem",
            CURLOPT_CAPATH => "/Applications/XAMPP/cacerts.pem",
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
        foreach ($videos as $videoId) {
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
                CURLOPT_CAINFO => "/Applications/XAMPP/cacerts.pem",
                CURLOPT_CAPATH => "/Applications/XAMPP/cacerts.pem",
                CURLOPT_FOLLOWLOCATION => TRUE
            ));
            $resp = curl_exec($curl);

            curl_close($curl);

            if ($resp) {
                $json = json_decode($resp);

                if ($json) {
                    echo("############### JSON decoded video ###############<br>");

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
                        echo("On " . $publishedOn . " , Replies :" . $replyCount);
                        echo("Chanel ID " . $authorChannelId);
                        echo("Chanel URL " . $authorChannelUrl);
                        echo("<hr>");

                        // 2. Analyse from text using Geotext
                        $geoResult = shell_exec(' python geotext/geo.py ' . $textDisplay);
                        echo("Geo result location: " . $geoResult . "<br>");

                        // 1. Can get from Chanel ID or textDisplay
                        $channelUrl = "https://www.googleapis.com/youtube/v3/channels?key=" .
                            $youtubeAPIKey . "&part=id,contentDetails,statistics,snippet&id=" . $authorChannelId;
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_URL => $channelUrl,
                            CURLOPT_USERAGENT => 'YouTube API Tester',
                            CURLOPT_SSL_VERIFYPEER => 1,
                            CURLOPT_SSL_VERIFYHOST => 0,
                            CURLOPT_CAINFO => "/Applications/XAMPP/cacerts.pem",
                            CURLOPT_CAPATH => "/Applications/XAMPP/cacerts.pem",
                            CURLOPT_FOLLOWLOCATION => TRUE
                        ));
                        $resp = curl_exec($curl);

                        curl_close($curl);

                        if ($resp) {
                            $json = json_decode($resp);
                            echo("<br>");

                            echo("######### Test #############");
                            print_r($json);
                            echo("######### End test #############");


                            if ($json) {
                                echo("############### JSON decoded Channel ###############<br>");
                                $channelItems = $json->items;
                                foreach ($channelItems as $channelItem) {
                                    $country = $channelItem->snippet->country;
                                    echo("Country " . $country . " <br> ");

                                }
                            }

                        }


                    }


                } else
                    exit("Error. could not parse JSON." . json_last_error_msg());


            } // if resp
        }
    }

    // Geocode using text analytic
    public function ytcomment3($keyword)
    {
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
        set_time_limit(60 * 3);

        $youtubeAPIKey = "AIzaSyA_sxi8v-wE-ZhLVZE7jCWdNUHfWjsnMHI";
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
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CAINFO => "/Applications/XAMPP/cacerts.pem",
            CURLOPT_CAPATH => "/Applications/XAMPP/cacerts.pem",
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
        foreach ($videos as $videoId) {
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
                CURLOPT_CAINFO => "/Applications/XAMPP/cacerts.pem",
                CURLOPT_CAPATH => "/Applications/XAMPP/cacerts.pem",
                CURLOPT_FOLLOWLOCATION => TRUE
            ));
            $resp = curl_exec($curl);

            curl_close($curl);

            if ($resp) {
                $json = json_decode($resp);

                if ($json) {
                    echo("############### JSON decoded video ###############<br>");

                    $items = $json->items;

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
                        echo("Chanel ID " . $authorChannelId);
                        echo("Chanel URL " . $authorChannelUrl);
                        echo("<hr>");

                        // 2. Analyse from text
                        // /from\s{1}(\w+)/g

                        // Install apt


                    }


                } else
                    exit("Error. could not parse JSON." . json_last_error_msg());


            } // if resp
        }
    }

}
