@extends('master')

@section('content')
    <div class="content">
        <h2>Results for <em>{{ $keyword }}</em></h2>
        <table class="table-striped table-responsive">
            <thead>
            <tr>
                <td></td>
                <td>Time</td>
                <td>Language</td>
                <td>Tweet</td>
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