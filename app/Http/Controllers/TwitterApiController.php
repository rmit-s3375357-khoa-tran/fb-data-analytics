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

        //print_r($results);

        /*****Analysing sentiments******/
        //$sentiments = $this->sentimentAnalysis($results);
        //list($tweetSentiments, $coordinates) = $this->analyseTweet($results);
        list($sentiments,$posCoordinates,$negCoordinates,$neuCoordinates) = $this->sentimentAnalysis($results);
        //list($sentiments,$posCoordinates,$negCoordinates,$neuCoordinates)=$this->analyseTweet($results);

        //return view('twitter', compact('keyword', 'results', 'sentiments', 'tweetSentiments','coordinates'));
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


    private function ytcomment($videoID)
    {
        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ . '"');
        }
        require_once __DIR__ . '/vendor/autoload.php';
        $OAUTH2_CLIENT_ID = '543042616561-qv0ebbpuu2di5011r5nugg6ubnmfnadb.apps.googleusercontent.com';
        $OAUTH2_CLIENT_SECRET = 'cHU1wlUGgcpKFvKNOP1boC_D';
        $VIDEO_ID = $videoID;


        $client = new Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);

        /*
        * This OAuth 2.0 access scope allows for full read/write access to the
         * authenticated user's account and requires requests to use an SSL connection.
        */
        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
            FILTER_SANITIZE_URL);
        $client->setRedirectUri($redirect);

        // Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);

        // Check if an auth token exists for the required scopes
        $tokenSessionKey = 'token-' . $client->prepareScopes();
        if (isset($_GET['code'])) {
            if (strval($_SESSION['state']) !== strval($_GET['state'])) {
                die('The session state did not match.');
            }

            $client->authenticate($_GET['code']);
            $_SESSION[$tokenSessionKey] = $client->getAccessToken();
            header('Location: ' . $redirect);
        }

        if (isset($_SESSION[$tokenSessionKey])) {
            $client->setAccessToken($_SESSION[$tokenSessionKey]);
        }
        // Check to ensure that the access token was successfully acquired.
        if ($client->getAccessToken()) {
            try {
                # All the available methods are used in sequence just for the sake of an example.

                // Call the YouTube Data API's commentThreads.list method to retrieve video comment threads.
                $videoCommentThreads = $youtube->commentThreads->listCommentThreads('snippet', array(
                    'videoId' => $VIDEO_ID,
                    'textFormat' => 'plainText',
                ));

                $parentId = $videoCommentThreads[0]['id'];

                // # Create a comment snippet with text.
                // $commentSnippet = new Google_Service_YouTube_CommentSnippet();
                // $commentSnippet->setTextOriginal($TEXT);
                // $commentSnippet->setParentId($parentId);

                // # Create a comment with snippet.
                // $comment = new Google_Service_YouTube_Comment();
                // $comment->setSnippet($commentSnippet);

                // # Call the YouTube Data API's comments.insert method to reply to a comment.
                // # (If the intention is to create a new top-level comment, commentThreads.insert
                // # method should be used instead.)
                // $commentInsertResponse = $youtube->comments->insert('snippet', $comment);


                // Call the YouTube Data API's comments.list method to retrieve existing comment replies.
                $videoComments = $youtube->comments->listComments('snippet', array(
                    'parentId' => $parentId,
                    'textFormat' => 'plainText',
                ));
                return $videoComments;
            } catch (Google_Service_Exception $e) {
                //$htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
                    //htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                //$htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
                    //htmlspecialchars($e->getMessage()));
            }
    }
    }
}
