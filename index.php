<?php

include 'getUserById.php';
include 'getStreams.php';
include 'getEnrichedStreams.php';
include 'getTopsOfTops.php';
include 'register.php';
include 'token.php';

header('Content-Type: application/json');

$basePath = $_SERVER['SCRIPT_NAME'];
$uri      = str_replace($basePath, '', strtok($_SERVER['REQUEST_URI'], '?'));
$uri      = str_replace('/VyV-200', '', $uri);

switch ($uri) {
    case '/analytics/user':
        if (empty($_GET['id'])) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode(
                ['error' => "Invalid or missing 'id' parameter.",
                        ]
            );
            exit();
        } else {
            getUserById($_GET['id']);
        }
        break;
    case '/analytics/streams':
        getStreams();
        break;
    case '/analytics/streams/enriched':
        if (empty($_GET['limit']) || $_GET['limit'] <= 0 || $_GET['limit'] > 20) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode(
                ['error' => "Invalid 'limit' parameter.",
                        ]
            );
            exit();
        } else {
            getEnrichedStreams();
        }
        break;
    case '/register':
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (empty($data['email'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'The email is mandatory'], JSON_PRETTY_PRINT);
            return;
        } else {
            register($data['email']);
        }
        break;
    case '/token':
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (empty($data['email'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'The email is mandatory'], JSON_PRETTY_PRINT);
            return;
        } elseif (empty($data['api_key'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'The api_key is mandatory'], JSON_PRETTY_PRINT);
            return;
        } else {
            $email   = $data['email'];
            $api_key = $data['api_key'];
            token($email, $api_key);
        }
        break;
    case '/analytics/topsofthetops':
        if (isset($_GET['since'])) {
            getTopOfTops($_GET['since']);
        } else {
            getTopOfTops(600);
        }
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
