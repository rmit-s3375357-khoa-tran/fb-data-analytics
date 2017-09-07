@extends('master')

@section('content')
    <!-- Google Map API -->
    <div id ="map"></div>
    {{--<script type="text/javascript">--}}
        {{--initMap = function() {--}}

            {{--var map = new google.maps.Map(document.getElementById('map'), {--}}
                {{--zoom: 3,--}}
                {{--center: {lat: -28.024, lng: 140.887}--}}
            {{--});--}}

            {{--var markers = locations.map(function(location, i) {--}}
                {{--return new google.maps.Marker({--}}
                    {{--position: location--}}
                {{--});--}}
            {{--});--}}

            {{--var markerCluster = new MarkerClusterer(map, markers,--}}
                    {{--{imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});--}}
        {{--};--}}
        {{--var locations = JSON.parse('{!! json_encode($coordinates) !!}');--}}
        {{--console.log(locations);--}}
    {{--</script>--}}

    <script type="text/javascript">
        initMap = function() {

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 3,
                center: {lat: -28.024, lng: 140.887}
            });
            // Positive coordinates

            var posMarkers = locations.map(function(location, i) {
                return new google.maps.Marker({
                    position: location
                });
            });
            var negMarkers = locations.map(function(location, i) {
                return new google.maps.Marker({
                    position: location
                });
            });
            var neuMarkers = locations.map(function(location, i) {
                return new google.maps.Marker({
                    position: location
                });
            });

            var posMarkerCluster = new MarkerClusterer(map, posMarkers,
                    {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m1'});
            var negMarkerCluster = new MarkerClusterer(map, negMarkers,
                    {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m2'});
            var neuMarkerCluster = new MarkerClusterer(map, neuMarkers,
                    {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m3'});
            };

        var locations = JSON.parse('{!! json_encode($posCoordinates) !!},{!! json_encode($negCoordinates) !!},{!! json_encode($neuCoordinates) !!}');
        console.log(locations);
    </script>

    <div class="content">
        <div class="title">Results for <em>{{ $keyword }}</em></div>

        <div id="graph" style="min-width: 310px; height: 400px; margin: 0 auto"></div>



        <script type="text/javascript">

            Highcharts.chart('graph', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Social Media Sentiment Analysis'
                },
                xAxis: {
                    categories: [
                        'Twitter with AZURE API',
                        'Twitter with Datumbox API',
                        'Youtube'
                    ],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'sentiment'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Positive',
                    data: [{{ $sentiments['positive'] }}, {{ $tweetSentiments['positive'] }}, 0]

                }, {
                    name: 'Negative',
                    data: [{{ $sentiments['negative']  }}, {{ $tweetSentiments['negative']  }}, 0]

                }, {
                    name: 'Neutral',
                    data: [{{ $sentiments['neutral']  }}, {{ $tweetSentiments['neutral']  }}, 0]

                }]
            });
        </script>

        @if($results)
            <div class="title">
                Results for <em>{{ $keyword }}</em>
            </div>
            <p>Results exported to csv files.</p>
            <table class="table-striped table-responsive">
                <thead>
                <tr>
                    <td></td>
                    <td><h2>Time</h2></td>
                    <td><h2>Tweet</h2></td>
                    <td><h2>User Location</h2></td>
                    <td><h2>User Timezone</h2></td>
                    <td><h2>Geo</h2></td>
                    <td><h2>Coordinates</h2></td>
                </tr>
                </thead>
                <tbody>
                @foreach($results as $index=>$result)
                    <tr>
                        <td>{{ $index+1 }}</td>
                        <?php
                        $timeCarbon = new \Carbon\Carbon($result['created_at']);
                        $time = $timeCarbon->toDateTimeString();
                        ?>
                        <td>{{ $time }}</td>
                        <td><div>{{ $result['tweet'] }}</div></td>
                        <td><div>{{ $result['user_location'] }}</div></td>
                        <td><div>{{ $result['user_timezone'] }}</div></td>
                        <td><div>{{ $result['geo'] }}</div></td>
                        <td><div>{{ $result['place_longitude'] .", ". $result['place_latitude']}}</div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="title">Streaming failed.</div>
        @endif
    </div>
@endsection