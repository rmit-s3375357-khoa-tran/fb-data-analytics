@extends('master')

@section('content')
    {{--<div class="content">--}}
    {{--<div><img src="{{ asset('images/round-blue-bigdata.png') }}"></div>--}}
    {{--<div class="title">Search for Tweets</div>--}}
    {{--<form action="twitter" method="post">--}}
    {{--<input type="hidden" name="_token" value="{{ csrf_token() }}">--}}
    {{--<label>Keyword </label><input name="keyword" required><br>--}}
    {{--<label>Number of Tweets </label><input name="count" type="number"><br>--}}
    {{--<label>Stop words (Separate words by comma) </label><input name="stopwords"><br>--}}
    {{--<input type="submit" name="submit" value="Search">--}}
    {{--</form>--}}
    {{--</div>--}}
    <div class="content">
        <div class="title">Search for Twitter</div>
        <form action="youtube" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <label>Keyword </label><input name="keyword"><br>
            <input type="submit" name="submit" value="Search">
        </form>
    </div>


@endsection