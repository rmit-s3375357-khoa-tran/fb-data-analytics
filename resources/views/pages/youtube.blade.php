@extends('pages.master')

@section('content')
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
                    </tr>

                @endforeach
                </tbody>
            </table>
        @else
            <div class="title">Streaming failed.</div>
        @endif
    </div>


@endsection