@extends('layouts.master')

@section('content')
    <div class="page-header">
        <h1>Seed Reviews</h1>
    </div>

    <div class="page-content">

        <form action="{{ URL::to('reviews/seed') }}" class="" role="form" method="post">
            <div class="form-inline">
                <div class="form-group">
                    {{ Form::label('text_count', 'Text Count', ['form-label']) }}
                    {{ Form::select('text_count', [100 => 100, 1000 => 1000, 10000 => 10000, 100000 => 100000, 1000000 => 1000000], 100, ['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('data_count', 'Data Count', ['form-label']) }}
                    {{ Form::select('data_count', [100 => 100, 1000 => 1000, 10000 => 10000, 100000 => 100000, 1000000 => 1000000], 100, ['class' => 'form-control']) }}
                </div>
                <div class="checkbox">
                    <label>
                        {{ Form::checkbox('truncate', 1, false, ['class' =>'']) }} Truncate
                    </label>
                </div>
                <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-adjust"></span> Seed</button>
            </div>
        </form>

    </div>
@stop