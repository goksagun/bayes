@extends('layouts.master')

@section('content')
    <div class="page-header">
        <h1>Update Review</h1>
    </div>

    <div class="page-content">

        <form action="{{ URL::to('reviews/update', [$review->id]) }}" class="" role="form" method="post">
            <div class="form-group">
                <textarea name="text" class="form-control" rows="3">{{ $review->text }}</textarea>
            </div>
            <div class="form-inline">
                <div class="form-group">
                    {{ Form::select('tag', $tags, $review->tag, ['class' => 'form-control']) }}
                </div>
                <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk"></span> Update</button>
            </div>
        </form>

    </div>
@stop