@extends('layouts.master')

@section('content')
    <div class="page-header">
        <h1>All Reviews</h1>
    </div>

    <div class="page-content">
        <table class="table table-responsive table-striped table-condensed table-hover">
            <thead>
                <th>#</th>
                <th>Text</th>
                <th>Tag</th>
                <th></th>
            </thead>
            <tbody>
                @foreach($reviews as $review)
                <tr>
                    <td>{{ $review->id }}</td>
                    <td><em>{{ $review->text }}</em></td>
                    <td>{{ $review->tag }}</td>
                    <td><a class="btn btn-info btn-xs" href="{{ URL::to('reviews/edit', [$review->id]) }}"><span class="glyphicon glyphicon-pencil"></span> Edit</a></td>
                    <td>
                        <form action="{{ URL::to('reviews/destroy', [$review->id]) }}" method="post" role="form">
                        <button type="submit" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash"></span> Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr collspan="3"></tr>
            </tfoot>
        </table>

        {{ $reviews->links()     }}
    </div>
@stop