<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->post('/register', 'Register\RegisterController@register');


$router->post('/token', 'Token\TokenController@getToken');


$router->get('/analytics/user', [
    'middleware' => 'auth',
    'uses'       => 'GetUserById\GetUserByIdController@getUser',
]);

$router->get('/analytics/streams', 'GetStreams\GetStreamsController@getStreams');

$router->get('/analytics/streams/enriched', [
    'middleware' => 'auth',
    'uses'       => 'GetEnrichedStreams\GetEnrichedStreamsController@getEnriched',
]);

$router->get('/analytics/topsofthetops', [
    'middleware' => 'auth',
    'uses'       => 'GetTopOfTops\GetTopOfTopsController@getTopOfTops',
]);
