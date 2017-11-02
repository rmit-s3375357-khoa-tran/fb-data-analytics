@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Update Azure API Keys</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/azure/update') }}">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('key1') ? ' has-error' : '' }}">
                                <label for="key1" class="col-md-4 control-label">Azure Key 1</label>

                                <div class="col-md-6">
                                    <input id="key1" class="form-control" name="key1" value="{{ old('key1') }}">

                                    @if ($errors->has('key1'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('key1') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('key2') ? ' has-error' : '' }}">
                                <label for="key2" class="col-md-4 control-label">Azure Key 2</label>

                                <div class="col-md-6">
                                    <input id="key2" class="form-control" name="key2" value="{{ old('key2') }}">

                                    @if ($errors->has('key2'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('key2') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-btn fa-pencil"></i> Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection