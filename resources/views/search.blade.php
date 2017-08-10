@extends('master')

@section('content')
    <div class="content">
        <div class="title">Search for Twitter</div>
        <form action="twitter" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <label>Keyword </label><input name="keyword"><br>
            <input type="submit" name="submit" value="Search">
        </form>
    </div>
@endsection