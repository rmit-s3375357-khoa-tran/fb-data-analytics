<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Symfony\Component\Process\Process;

class FacebookApiController extends Controller
{
    public function collect(Request $request)
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
        $count = $request->count > 0 ? $request->count : 100;
        $startingDate = $request->date != "" ?
            Carbon::parse($request->date)->toDateString() :
            Carbon::today()->subMonth()->toDateString();
        $today = Carbon::today()->toDateString();

        // execute python script using process, get posts from page entered
        $process = new Process('python fbScrapper/get_fb_posts_fb_page.py ' . $keyword . ' ' . $startingDate . ' ' . $today);
        $process->run();

        // execute python script using process, get comments from posts collected
        $process = new Process('python fbScrapper/get_fb_comments_from_fb.py ' . $keyword . ' ' . $count);
        $process->run();

        return json_encode([
            'success' => true,
            'path' => asset('/results/facebook_'.$keyword.'.csv')
        ]);
    }
}
