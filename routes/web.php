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


$router->get('/analytics/streams', function () {
    require_once __DIR__ . '/../getStreams.php';
    getStreams();
});

$router->get('/analytics/streams/enriched', function () {
    require_once __DIR__ . '/../getEnrichedStreams.php';
    getEnrichedStreams();
});

$router->get('/analytics/topsofthetops', function () {
    require_once __DIR__ . '/../getTopsOfTops.php';
    $since = isset($_GET['since']) ? $_GET['since'] : 600;
    getTopOfTops($since);
});
