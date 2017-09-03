@extends('master')

@section('content')
    <div class="content">
        @if($results)
            <div class="title">
                Results for <em>{{ $keyword }}</em><br>
                exported into csv files.
            </div>
        @else
            <div class="title">Streaming failed.</div>
        @endif
    </div>
@endsection