@extends('pages.master')

@section('content')
    <input id="_token" value="{{ csrf_token() }}" hidden>
    @include('components.search.search')
    @include('components.results.results')
@endsection

@section('scripts')
    <script src="{{ asset('js/search.js') }}"></script>
    <script src="{{ asset('js/twitter.js') }}"></script>
    <script src="{{ asset('js/youtube.js') }}"></script>
@endsection