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
        $this->apiKey = config('setting.youtube.key');
    }

    public function search(Request $request)
    {
        // keyword has to be set
        if ($request->keyword == "") {
            return json_encode([
                'success' => false,
                'message' => 'Keyword is required.'
            ]);
        }

        // extract useful data from request
        $keyword = $request->keyword;
        $count = ($request->count != "" && $request->count > 0) ? $request->count : 3;

        // get data from api
        $endpoint = "https://www.googleapis.com/youtube/v3/search?" .
            "key=" . $this->apiKey .
            "&part=id,snippet" .
            "&q=" . $keyword .
            "&maxResults=" . $count * 2 . // to collect twice results for filtering
            "&type=video";

        // get response from api and return if failed
        $response = $this->sendRequest($endpoint);
        if (!$response['success']) {
            return json_encode($response);
        }

        // prepare result when successful response
        $items = $response['result'];
        $videoDetails = [];
        foreach ($items as $item) {
            $publishedAt = Carbon::parse($item->snippet->publishedAt);
            $startingDate = $request->date != "" ?
                Carbon::parse($request->date) :
                Carbon::today()->subMonth();

            // when it's published after starting date and not reach count yet
            if ($publishedAt >= $startingDate && count($videoDetails) < $count) {
                $videoDetails[] = [
                    'videoId' => $item->id->videoId,
                    'title' => $item->snippet->title,
                    'description' => $item->snippet->description,
                    'publishedAt' => $publishedAt->toDateTimeString()
                ];
            }
        }

        if (count($videoDetails)) {
            return json_encode([
                'success' => true,
                'data' => $videoDetails
            ]);
        } else {
            return json_encode([
                'success' => true,
                'message' => "NoVideoFound"
            ]);
        }
    }

    public function collect(Request $request)
    {
        // extract all params passed in
        $videoIds = $request->videoIds;
        $count = $request->count > 0 ? $request->count : 100;

        // video ids have to be set
        if ($videoIds == "") {
            return json_encode([
                'success' => false,
                'message' => 'Please select at least one video.'
            ]);
        }

        // holder for results
        $comments = [];

        // calculate how many comments to get from each video
        $total = $count;
        $numOfVideos = count($videoIds);
        $avgNumOfComments = (int)$total / $numOfVideos;

        // collect comments for each video
        foreach ($videoIds as $index => $videoId) {
            // calculate counter index this video can reach for num of comments
            $counter = ($index + 1) * $avgNumOfComments;

            // prepare endpoint
            $endpoint = "https://www.googleapis.com/youtube/v3/commentThreads?" .
                "key=" . $this->apiKey .
                "&part=id,snippet,replies" .
                "&videoId=" . $videoId;

            // get response from api and only continue when successful
            $response = $this->sendRequest($endpoint);
            if ($response['success']) {
                // prepare result when successful response
                $items = $response['result'];
                foreach ($items as $item) {
                    // break out to get comments for next video
                    if (count($comments) > $counter) {
                        break;
                    }

                    $comment = $this->extractUsefulFields($item->snippet->topLevelComment->snippet);
                    if ($comment) {
                        $comments[] = $comment;
                    }
                }
            }
        }

        // save both raw data and processed data into csv, pre-process with stop word
        if (count($comments)) {
            // header for csv
            $header = [
                'created_at',
                'text',
                'author_display_name',
                'author_channel_url',
                'author_channel_id'
            ];

            return $this->save($request, $comments, $header, 'youtube');
        } else {
            return json_encode([
                'success' => false,
                'message' => 'Failed to get comments.'
            ]);
        }

    }

    public function addCustomUrls(Request $request)
    {
        // return false when urls not set
        if (!isset($request->urls) && $request->urls == "") {
            return json_encode([
                'success' => false,
                'message' => 'URL(s) not found.'
            ]);
        }

        // get video info from urls
        $items = [];
        $urls = explode(",", $request->urls);
        foreach ($urls as $url) {
            // get the video id
            parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars);
            $id = $my_array_of_vars['v'];

            // prepare endpoint
            $endpoint = 'https://www.googleapis.com/youtube/v3/videos?' .
                "key=" . $this->apiKey .
                "&part=snippet" .
                '&id=' . $id;

            // get response from api and return if failed
            $response = $this->sendRequest($endpoint);
            if ($response['success'] && isset($response['result'][0])) {
                array_push($items, $response['result'][0]);
            }
        }

        // when all urls are invalid
        if (!count($items)) {
            return json_encode([
                'success' => false,
                'message' => 'URL(s) not found.'
            ]);
        }

        // get response from api and add to array only when successful
        $videoDetails = [];
        foreach ($items as $item) {
            $publishedAt = Carbon::parse($item->snippet->publishedAt);
            $videoDetails[] = [
                'videoId' => $item->id,
                'title' => $item->snippet->title,
                'description' => $item->snippet->description,
                'publishedAt' => $publishedAt->toDateTimeString()
            ];
        }

        return json_encode([
            'success' => true,
            'data' => $videoDetails
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
        if ($apiResponse->getStatusCode() != 200) {
            return [
                'success' => false,
                'message' => 'Response failed.'
            ];
        }

        // in case of empty response
        $response = json_decode((string)$apiResponse->getBody());
        if (!$response) {
            return [
                'success' => false,
                'message' => 'Empty response.'
            ];
        }

        // return all items from response
        return [
            'success' => true,
            'result' => $response->items
        ];
    }

    private function extractUsefulFields($result)
    {
        $fields = null;

        // only extract info needed
        if (isset($result->publishedAt)) {
            $text = str_replace(
                array("\r\n", "\n", "\r", "'", "`", '"'),
                '', $result->textOriginal);

            $authorDisplayName = str_replace(
                array("\r\n", "\n", "\r", "'", "`", '"')
                , " ", $result->authorDisplayName);

            $fields = [
                'created_at' => $result->publishedAt,
                'text' => $text,
                'author_display_name' => $authorDisplayName,
                'author_channel_url' => $result->authorChannelUrl,
                'author_channel_id' => $result->authorChannelId->value
            ];
        }

        return $fields;
    }
}
