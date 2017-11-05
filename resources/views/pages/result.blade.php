@extends('pages.master')

@section('content')
    <script type="text/javascript">
        var assetBaseUrl = '{{ asset('') }}',
                positive = JSON.parse('{!! json_encode($posCoordinates) !!}'),
                negative = JSON.parse('{!! json_encode($negCoordinates) !!}'),
                neutral = JSON.parse('{!! json_encode($neuCoordinates) !!}'),
                pos_twitter_sentiment = parseInt("{{$sentiments['positive']}}"),
                neg_twitter_sentiment = parseInt("{{ $sentiments['negative']}}"),
                neu_twitter_sentiment = parseInt("{{ $sentiments['neutral']}}"),
                pos_yt_sentiment = parseInt("{{$YTsentiments['positive']}}"),
                neg_yt_sentiment = parseInt("{{ $YTsentiments['negative']  }}"),
                neu_yt_sentiment = parseInt("{{ $YTsentiments['neutral']  }}"),
                pos_fb_sentiment = parseInt("{{$FBsentiments['positive']}}"),
                neg_fb_sentiment = parseInt("{{ $FBsentiments['negative']  }}"),
                neu_fb_sentiment = parseInt("{{ $FBsentiments['neutral']  }}"),
                twitter_data = JSON.parse('{!! json_encode($results) !!}'),
                youtube_data = JSON.parse('{!! json_encode($YTresults)!!}'),
                facebook_data = JSON.parse('{!! json_encode($FBresults)!!}');
        console.log(twitter_data);
    </script>


    <div id ="map"></div>

    <div class="container">
        <div class="title">Results for <em>{{$keyword}}</em></div>
        <div class="row">
            <div class="col-md-6"><div id="graph"></div></div>
            <div class="col-md-6"><div id="pie"></div></div>
        </div>
        @include('pages.data')
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('js/result/map.js') }}"></script>
    <script src="{{ asset('js/result/chart-markerclusterer.js') }}"></script>
    <script src="{{ asset('js/result/chart.js') }}"></script>
    <script src="{{ asset('js/result/view-data.js') }}"></script>
    <script src="{{ asset('js/result/pie-chart.js') }}"></script>
    <script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js'></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZKtWWEz1mLZZKGl9jdLUFHbPL5nuY5AE&callback=initMap"></script>
@endsection