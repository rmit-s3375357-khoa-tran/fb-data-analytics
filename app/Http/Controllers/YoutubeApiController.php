<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class YoutubeApiController extends Controller
{
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
