<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Display all SQL executed in Eloquent
Event::listen('illuminate.query', function($query)
{
//    print($query);
});

Route::get('/', 'HomeController@getIndex');
Route::post('train', 'HomeController@postTrain');
Route::post('classify', 'HomeController@postClassify');

Route::controller('reviews', 'ReviewController');

Route::controller('faker', 'FakerController');
