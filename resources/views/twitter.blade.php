@extends('master')

@section('content')
    <div class="content">
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