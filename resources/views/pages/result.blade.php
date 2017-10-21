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

        console.log("Testing");
        console.log(positive);
        console.log(negative);
        console.log(neutral);

        initMap = function() {

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 3,
                center: positive[0]
            });

            var opt = {
                "styles" : [
                    {textColor: "black", textSize: 15, height: 60, width: 60},
                    {textColor: "black", textSize: 15, height: 70, width: 70},
                    {textColor: "black", textSize: 15, height: 80, width: 80}
                ],

                "legend": {
                    "Positive" : "#008eff",
                    "Negative" : "#ff0000",
                    "Neutral"  : "#ffba00"
                }
            };

            var twitter_pos_pin = {
                url: assetBaseUrl+ 'images/pins/pos_pin.png',
                scaledSize: new google.maps.Size(30, 30)
            };

            var twitter_neg_pin = {
                url: assetBaseUrl+'images/pins/neg_pin.png',
                scaledSize: new google.maps.Size(30, 30)
            };

            var twitter_neu_pin = {
                url: assetBaseUrl+'images/pins/neutral_pin.png',
                scaledSize: new google.maps.Size(30, 30)
            };


            var markers = [];
            var latLng = null;
            var marker = null;

            for (var key in positive)
            {
                latLng = new google.maps.LatLng(positive[key].lat, positive[key].lng);
                marker = new google.maps.Marker({
                    position: latLng,
                    icon: twitter_pos_pin,
                    title: "Positive"
                });
                markers.push(marker);
            }

            for (var key in negative)
            {
                latLng = new google.maps.LatLng(negative[key].lat, negative[key].lng);
                marker = new google.maps.Marker({
                    position: latLng,
                    icon: twitter_neg_pin,
                    title: "Negative"
                });
                markers.push(marker);
            }

            for (var key in neutral)
            {
                latLng = new google.maps.LatLng(neutral[key].lat, neutral[key].lng);
                marker = new google.maps.Marker({
                    position: latLng,
                    icon: twitter_neu_pin,
                    title: "Neutral"
                });
                markers.push(marker);
            }

            var markerCluster = new MarkerClusterer(map, markers, opt);
        };

        google.load("visualization", "1", {packages: ["corechart"]});
        google.setOnLoadCallback(initMap);

    </script>

    <!-- Google Map API -->
    <div id ="map"></div>

@endsection

@section('content')
    <script src="{{ asset('js/result/map.js') }}"></script>
    <script src="{{ asset('js/result/chart-markerclusterer.js') }}"></script>
    <script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js'></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZKtWWEz1mLZZKGl9jdLUFHbPL5nuY5AE&callback=initMap"></script>
@endsection