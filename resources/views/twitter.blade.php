@extends('master')

@section('content')
    <div class="content">
        <p>Positive tweets {{ $sentiments['positive'] }}</p>
        <p>Negative tweets {{ $sentiments['negative']  }}</p>
        <p>>Neutral tweets {{ $sentiments['neutral']  }}</p>
        <div class="title">Results for <em>{{ $keyword }}</em></div>
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
                        <td><div>{{ $result->text }}</div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection