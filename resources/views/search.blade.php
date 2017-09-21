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
        <input id="_token" value="{{ csrf_token() }}" hidden>
        <div class="col-xs-12">
            <div class="col-xs-4 text-center">
                <p>
                    <img class="social-media-logo" src="{{ asset('images/logos/logo_twitter.png') }}">
                </p>
                <p>
                    <label>Number of Tweets</label><input id="number-of-tweets" type="number"><br>
                    <small class="text-gray">By default 100</small>
                </p>
                <p>
                    <button id="collect-twitter" class="btn btn-primary btn-lg">
                        Collect <i class="fa fa-twitter" aria-hidden="true"></i>
                    </button>
                </p>
                <p class="alert alert-success" id="twitter-alert" style="display:none">
                    Yo! Tweets ready! Download <span id="twitter-download-link"></span> :)
                </p>
            </div>
            <div class="col-xs-4 text-center">
                <p>
                    <img class="social-media-logo" src="{{ asset('images/logos/Facebook-Logo-2.png') }}">
                </p>
                <p>
                    <label>Number of Pages</label><input id="number-of-facebook-pages" type="number"><br>
                    <small class="text-gray">By default 3</small>
                </p>
                <p>
                    <button id="collect-facebook" class="btn btn-primary btn-lg">
                        Collect <i class="fa fa-facebook-official" aria-hidden="true"></i>
                    </button>
                </p>
            </div>
            <div class="col-xs-4 text-center">
                <p>
                    <img class="social-media-logo" src="{{ asset('images/logos/youtube_2017_logo.png') }}">
                </p>
                <p>
                    <label>Number of Videos</label><input id="number-of-youtube-videos" type="number"><br>
                    <small class="text-gray">By default 3</small>
                </p>
                <p>
                    <button id="collect-youtube" class="btn btn-primary btn-lg">
                        Collect <i class="fa fa-youtube" aria-hidden="true"></i>
                    </button>
                </p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/search.js') }}"></script>
@endsection