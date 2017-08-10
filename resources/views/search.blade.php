@extends('master')

@section('content')
    <div class="content">
        <div><img src="{{ asset('images/round-blue-bigdata.png') }}"></div>
        <div class="title">Search for Tweets</div>
        <form action="twitter" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <label>Keyword </label><input name="keyword">
            <input type="submit" name="submit" value="Search">
        </form>
    </div>
@endsection