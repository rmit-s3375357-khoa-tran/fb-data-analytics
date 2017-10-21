@extends('pages.master')

@section('content')
    <script type="text/javascript">
        var assetBaseUrl = '{{ asset('') }}';
        var positive = JSON.parse('{!! json_encode($posCoordinates) !!}');
        var negative = JSON.parse('{!! json_encode($negCoordinates) !!}');
        var neutral = JSON.parse('{!! json_encode($neuCoordinates) !!}');
        var pos_twitter_sentiment = parseInt("{{$sentiments['positive']}}");
        var neg_twitter_sentiment = parseInt("{{ $sentiments['negative']  }}");
        var neu_twitter_sentiment = parseInt("{{ $sentiments['neutral']  }}");
        var twitter_data = '{!! json_encode($results) !!}';

    </script>

    <!-- Google Map API -->
    <div id ="map"></div>

@endsection

@section('scripts')
    <script src="{{ asset('js/result/map.js') }}"></script>
    <script src="{{ asset('js/result/chart-markerclusterer.js') }}"></script>
    <script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js'></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZKtWWEz1mLZZKGl9jdLUFHbPL5nuY5AE&callback=initMap"></script>
@endsection