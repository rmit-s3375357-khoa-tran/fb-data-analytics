@extends('master')

@section('content')
    <!-- Google Map API -->
    <div id ="map"></div>

    <script type="text/javascript">
        initMap = function() {

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 3,
                center: {lat: -28.024, lng: 140.887}
            });
            // Positive coordinates

            var pos_image = {
                url: '{{ asset('images/pins/pos_pin.png') }}',
                // This marker is 20 pixels wide by 32 pixels high.
                scaledSize: new google.maps.Size(30, 30)
            };

            var neg_image = {
                url: '{{ asset('images/pins/neg_pin.png') }}',
                // This marker is 20 pixels wide by 32 pixels high.
                scaledSize: new google.maps.Size(30, 30)
            };

            var neu_image = {
                url: '{{ asset('images/pins/neutral_pin.png') }}',
                // This marker is 20 pixels wide by 32 pixels high.
                scaledSize: new google.maps.Size(30, 30)
            };

            var pos_markers = positive.map(function(location) {
                return new google.maps.Marker({
                    position: location,
                    icon: pos_image
                });
            });

            var neg_markers = negative.map(function(location) {
                return new google.maps.Marker({
                    position: location,
                    icon: neg_image
                });
            });

            var neu_markers = neutral.map(function(location) {
                return new google.maps.Marker({
                    position: location,
                    icon: neu_image
                });
            });

// Add a marker clusterer to manage the markers.
            var markerCluster = new MarkerClusterer(map, pos_markers,
                    {imagePath: '{{ asset('images/cluster/positive') }}',
                        gridSize: 50});

            var markerCluster = new MarkerClusterer(map, neg_markers,
                    {imagePath: '{{ asset('images/cluster/negative') }}',
                        gridSize: 50});

            var markerCluster = new MarkerClusterer(map, neu_markers,
                    {imagePath: '{{ asset('images/cluster/neutral') }}',
                        gridSize: 50});
        };

        var positive = JSON.parse('{!! json_encode($posCoordinates) !!}');
        var negative = JSON.parse('{!! json_encode($negCoordinates) !!}');
        var neutral = JSON.parse('{!! json_encode($neuCoordinates) !!}');

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
                        'Twitter',
                        'Facebook',
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
                    data: [{{ $sentiments['positive'] }}, 0, 0]

                }, {
                    name: 'Negative',
                    data: [{{ $sentiments['negative']  }}, 0, 0]

                }, {
                    name: 'Neutral',
                    data: [{{ $sentiments['neutral']  }}, 0, 0]

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