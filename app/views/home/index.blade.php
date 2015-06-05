@extends('layouts.master')

@section('content')
    <div class="page-header">
        <h1>Train Review</h1>
    </div>

    <div class="page-content">

        <form action="{{ URL::to('train') }}" class="" role="form" method="post">
            <div class="form-group">
                <textarea name="text" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-inline clearfix">
                <div class="pull-left">
                    <div class="form-group">
                        {{ Form::select('tag', $tags, null, ['class' => 'form-control']) }}
                    </div>
                    <button type="submit" data-loading-text="Processing..." class="btn btn-primary btn-lg btn-train" autocomplete="off">Train</button>
                </div>
                <div class="pull-right">
                    <div class="form-group">
                        {{ Form::select('method', Config::get('bayes.methods'), Config::get('bayes.search.method'), ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::select('split', Config::get('bayes.splits'), Config::get('bayes.search.split'), ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::select('n-gram', $nGrams, 0, ['class' => 'form-control']) }}
                    </div>
                    <button type="submit" data-loading-text="Processing..." class="btn btn-info btn-lg btn-classify" autocomplete="off">Clasify</button>
                </div>
            </div>
        </form>

        <br/>

        <div class="result" style="display: none">
            <div class="well text-center">
                <h1></h1>
            </div>
        </div>

    </div>
@stop

@section('script')
    <script>
        $(function () {
            submitForm();
        });

        function submitForm() {

            $('.btn-train').click(function (e) {
                e.preventDefault();

                var $btn = $(this);

                $btn.button('loading');

                var $form = $('form');
                var $data = $form.serialize();

                train($data, $btn);
            });

            $('.btn-classify').click(function (e) {
                e.preventDefault();

                var $btn = $(this);

                $btn.button('loading');

                var $form = $('form');
                var $data = $form.serialize();

                classify($data, $btn);
            });
        }

        function train($data, $btn) {

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            var $jqxhr = $.post('/train', $data);

            // Perform other work here ...

            // Set another completion function for the request above
            $jqxhr.always(function($response) {
                setTimeout(function () {
                    var $alert = $response.alert;

                    notification($alert.type, $alert.message);

                    $btn.button('reset');
                }, 1000);
            });
        }

        function classify( $data, $btn ) {
            var $container = $('.result');

            $container.fadeOut();

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            var $jqxhr = $.post('/classify', $data);

            // Perform other work here ...

            // Set another completion function for the request above
            $jqxhr.always(function($response) {
                if ($response.success == true) {
                    var $data = $response.data;

                    var $container = $('.result');
                    var $template = '';
                    $template += '<div class="hero-unit text-center" style="padding: 20px;">';
                    $template += '<h1>'+ $data.probability +'</h1>';
                    $template += '<div class="row">';
                    $template += '<div class="col-lg-4">';
                    $template += '<h3>ham</h3>';
                    $template += '<code>count: '+ $data.data.ham.count +'</code><br/>';
                    $template += '<code>rate: '+ $data.data.ham.rate +'</code><br/>';
                    $template += '<code>percent: '+ $data.data.ham.percent +'</code>';
                    $template += '</div>';
                    $template += '<div class="col-lg-4">';
                    $template += '<h3>spam</h3>';
                    $template += '<code>count: '+ $data.data.spam.count +'</code><br/>';
                    $template += '<code>rate: '+ $data.data.spam.rate +'</code><br/>';
                    $template += '<code>percent: '+ $data.data.spam.percent +'</code>';
                    $template += '</div>';
                    $template += '<div class="col-lg-4">';
                    $template += '<h3>natural</h3>';
                    $template += '<code>count: '+ $data.data.natural.count +'</code><br/>';
                    $template += '<code>rate: '+ $data.data.natural.rate +'</code><br/>';
                    $template += '<code>percent: '+ $data.data.natural.percent +'</code>';
                    $template += '</div>';
                    $template += '</div>';
                    $template += '</div>';

                    $container.html($template).hide().fadeIn();
                } else {
                    var $alert = $response.alert;

                    notification($alert.type, $alert.message);
                }

                $btn.button('reset');
            });
        }

        function notification( $type, $message ) {

            var $type = $type || 'error';
            var $message = $message || 'Error';
            var $template = '';

            if ($type == 'error') $type = 'danger';

            var $container = $('.container-alert');

            $template += '<div class="alert alert-' + $type + ' alert-dismissible" role="alert">';
            $template += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
            $template += $message;
            $template += '</div>';

            $container.html($template).hide().fadeIn();

            setTimeout(function () {
                var $alert = $('.alert');

                $alert.fadeOut();
            }, 5000);
        }
    </script>
@stop