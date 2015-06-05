@extends('layouts.master')

@section('content')
    <div class="page-header">
        <h1>Faker Get a New Review</h1>
    </div>

    <div class="page-content">

        <form action="/faker/generate" class="" role="form" method="post">
            <div class="form-group">
                <textarea name="text" class="form-control" rows="3">{{ $text }}</textarea>
            </div>
            <button type="submit" data-loading-text="Processing..." class="btn btn-primary btn-lg btn-generate" autocomplete="off">Generate</button>
        </form>

    </div>
@stop

@section('script')
    <script>
        $(function () {
            submitForm();
        });

        function submitForm() {

            $('.btn-generate').click(function (e) {
                e.preventDefault();

                var $btn = $(this);

                $btn.button('loading');

                var $form = $('form');

                var $action = $form.attr('action');
                var $data = $form.serialize();

                faker($action, $data, $btn);
            });
        }

        function faker( $action, $data, $btn ) {

            var $action = $action || '/faker/generate';

            // Assign handlers immediately after making the request,
            // and remember the jqxhr object for this request
            var $jqxhr = $.post($action, $data);

            // Perform other work here ...

            // Set another completion function for the request above
            $jqxhr.always(function($response) {
                setTimeout(function () {
                    var $data = $response.data;
                    var $alert = $response.alert;

                    var $output = $('textarea');

                    $output.val($data);

                    notification($alert.type, $alert.message);

                    $btn.button('reset');
                }, 1000);
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