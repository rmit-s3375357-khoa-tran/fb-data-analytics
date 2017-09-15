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

    public function ytcomment($videoID)
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
                }


            } else
                exit("Error. could not parse JSON." . json_last_error_msg());


        } // if resp

    }
//    private function ytcomment($videoID)
//    {
//        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
//            throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ . '"');
//        }
//        require_once __DIR__ . '/vendor/autoload.php';
//
//
//        $OAUTH2_CLIENT_ID = '543042616561-qv0ebbpuu2di5011r5nugg6ubnmfnadb.apps.googleusercontent.com';
//        $OAUTH2_CLIENT_SECRET = 'cHU1wlUGgcpKFvKNOP1boC_D';
//        $VIDEO_ID = $videoID;
//
//
//        $client = new Google_Client();
//        $client->setClientId($OAUTH2_CLIENT_ID);
//        $client->setClientSecret($OAUTH2_CLIENT_SECRET);
//
//        /*
//        * This OAuth 2.0 access scope allows for full read/write access to the
//         * authenticated user's account and requires requests to use an SSL connection.
//        */
//        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
//        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
//            FILTER_SANITIZE_URL);
//        $client->setRedirectUri($redirect);
//
//        // Define an object that will be used to make all API requests.
//        $youtube = new Google_Service_YouTube($client);
//
//        // Check if an auth token exists for the required scopes
//        $tokenSessionKey = 'token-' . $client->prepareScopes();
//        if (isset($_GET['code'])) {
//            if (strval($_SESSION['state']) !== strval($_GET['state'])) {
//                die('The session state did not match.');
//            }
//
//            $client->authenticate($_GET['code']);
//            $_SESSION[$tokenSessionKey] = $client->getAccessToken();
//            header('Location: ' . $redirect);
//        }
//
//        if (isset($_SESSION[$tokenSessionKey])) {
//            $client->setAccessToken($_SESSION[$tokenSessionKey]);
//        }
//        // Check to ensure that the access token was successfully acquired.
//        if ($client->getAccessToken()) {
//            try {
//                # All the available methods are used in sequence just for the sake of an example.
//
//                // Call the YouTube Data API's commentThreads.list method to retrieve video comment threads.
//                $videoCommentThreads = $youtube->commentThreads->listCommentThreads('snippet', array(
//                    'videoId' => $VIDEO_ID,
//                    'textFormat' => 'plainText',
//                ));
//
//                $parentId = $videoCommentThreads[0]['id'];
//
//                // Call the YouTube Data API's comments.list method to retrieve existing comment replies.
//                $videoComments = $youtube->comments->listComments('snippet', array(
//                    'parentId' => $parentId,
//                    'textFormat' => 'plainText',
//                ));
//                return $videoComments;
//            } catch (Google_Service_Exception $e) {
//                //$htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
//                //htmlspecialchars($e->getMessage()));
//            } catch (Google_Exception $e) {
//                //$htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
//                //htmlspecialchars($e->getMessage()));
//            }
//        }
//    }
}
