<?php

include 'getUserById.php';
include 'getStreams.php';
include 'getEnrichedStreams.php';
include 'register.php';
include 'token.php';

$uri = strtok($_SERVER["REQUEST_URI"], '?');
header('Content-Type: application/json');

if ($_SERVER['SERVER_NAME'] == 'localhost') {
    switch ($uri) {
        case "/Entrega01-TwitchAnalytics/index.php/analytics/user":
            getUserById($_GET['id']);
            break;
        case "/Entrega01-TwitchAnalytics/index.php/analytics/streams":
            getStreams();
            break;
        case "/index.php/analytics/streams/enriched":
            getEnrichedStreams($_GET['limit']);
            break;
        case "/Entrega01-TwitchAnalytics/index.php/analytics/register":
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            $email = $data['email'];
            register($email);
            break;
        case "/Entrega01-TwitchAnalytics/index.php/token":
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            $email = $data['email'];
            $api_key = $data['api_key'];
            token($email, $api_key);
            break;
    }
} else{
    switch ($uri) {
        case "/analytics/user":
            getUserById($_GET['id']);
            break;
        case "/analytics/streams":
            getStreams();
            break;
        case "/analytics/streams/enriched":
            echo $_GET['limit'];
            getEnrichedStreams($_GET['limit']);
            break;
        case "/register":
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            $email = $data['email'];
            register($email);
            break;
        case "/token":
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            $email = $data['email'];
            $api_key = $data['api_key'];
            token($email, $api_key);
            break;
    }
}

