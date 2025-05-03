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

$router->post('/register', function () {
    require __DIR__ . '/../register.php';
    register();
});

$router->post('/token', function () {
    require_once __DIR__ . '/../token.php';

    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input'], JSON_PRETTY_PRINT);
        return;
    }

    if (empty($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'The email is mandatory'], JSON_PRETTY_PRINT);
        return;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'The email must be a valid email address'], JSON_PRETTY_PRINT);
        return;
    }

    if (empty($data['api_key'])) {
        http_response_code(400);
        echo json_encode(['error' => 'The api_key is mandatory'], JSON_PRETTY_PRINT);
        return;
    }

    token($data['email'], $data['api_key']);
});

$router->get('/analytics/user', function () {
    require_once __DIR__ . '/../getUserById.php';

    if (empty($_GET['id'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => "Invalid or missing 'id' parameter."], JSON_PRETTY_PRINT);
        return;
    }

    getUserById($_GET['id']);
});

$router->get('/analytics/streams', function () {
    require_once __DIR__ . '/../getStreams.php';
    getStreams();
});

$router->get('/analytics/streams/enriched', function () {
    require_once __DIR__ . '/../getEnrichedStreams.php';
    getEnrichedStreams();
});
