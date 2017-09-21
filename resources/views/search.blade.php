@extends('master')

@section('content')
    <div class="page-header text-center">
        <img src="{{ asset('images/logos/GrabSentiment.png') }}">
        <div class="text-uppercase text-large">Enter Your Keyword*: <input id="keyword" type="text"></div>
        <div>
            <div class="text-uppercase text-large">
                Set Your Stop Words: <input id="stop-words" type="text">
            </div>
            <small class="text-gray">Separate stop words by comma</small>
        </div>
    </div>
    <div class="container">
        <div class="col-xs-12">
            <div class="col-xs-4 text-center">
                <img class="social-media-logo" src="{{ asset('images/logos/logo_twitter.png') }}">
                <div>
                    <br><label>Number of Tweets</label><input id="number-of-tweets" type="number">
                    <br><small class="text-gray">By default 100</small>
                </div>
                <div>
                    <br><button class="btn btn-primary btn-lg">Collect Tweets</button>
                </div>
            </div>
            <div class="col-xs-4 text-center">
                <img class="social-media-logo" src="{{ asset('images/logos/Facebook-Logo-2.png') }}">
                <div>
                    <br><label>Number of Pages</label><input id="number-of-facebook-pages" type="number">
                    <br><small class="text-gray">By default 3</small>
                </div>
                <div>
                    <br><button class="btn btn-primary btn-lg">Collect Comments</button>
                </div>
            </div>
            <div class="col-xs-4 text-center">
                <img class="social-media-logo" src="{{ asset('images/logos/youtube_2017_logo.png') }}">
                <div>
                    <br><label>Number of Videos</label><input id="number-of-youtube-videos" type="number">
                    <br><small class="text-gray">By default 3</small>
                </div>
                <div>
                    <br><button class="btn btn-primary btn-lg">Collect Comments</button>
                </div>
            </div>
        </div>
    </div>
@endsection