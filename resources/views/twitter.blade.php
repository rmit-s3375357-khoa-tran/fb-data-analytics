@extends('master')

@section('content')
    <div class="content">
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>




        <div class="title">Results for <em>{{ $keyword }}</em></div>

        <div id="graph" style="min-width: 310px; height: 400px; margin: 0 auto"></div>


        <!-- Google Map API -->
        <div id ="map"></div>

        <script>
            initMap();

            function initMap(){
                //var coordinate = JSON.parse({{ json_encode($coordinate) }});

                var map = new google.maps.Map(document.getElementById('map'), {

                    center: new google.maps.LatLng({{$coordinate["lon"] , $coordinate["lat"]}}),
                    zoom: 12
                });


//                var GraphCoordinate = new google.maps.LatLng(coordinate);
//                var map = new google.maps.Map(document.getElementById('map'), {
//                    center: GraphCoordinate,
//                    zoom: 3
//                });
            }
        </script>
        {{--<script async defer src="https://maps.googleapis.com/maps/api/js?callback=initMap" type="text/javascript"></script>--}}

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZKtWWEz1mLZZKGl9jdLUFHbPL5nuY5AE&callback=initMap">

        </script>


        <!-- End Google Map API -->

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


        <table class="table-striped table-responsive">
            <thead>
            <tr>
                <td></td>
                <td><h2>Time</h2></td>
                <td><h2>Language</h2></td>
                <td><h2>Tweet</h2></td>
            </tr>
            </thead>
            <tbody>
            @foreach($results as $index=>$result)
                <tr>
                    <td>{{ $index+1 }}</td>
                    <?php
                    $timeCarbon = new \Carbon\Carbon($result->created_at);
                    $time = $timeCarbon->toDateTimeString();
                    ?>
                    <td>{{ $time }}</td>
                    <td>{{ $result->lang }}</td>
                    <td>
                        <div>{{ $result->text }}</div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection