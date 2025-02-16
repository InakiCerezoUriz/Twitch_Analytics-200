<?php

include 'getUserById.php';
include 'getStreams.php';
include 'getEnrichedStreams.php';
include 'register.php';
include 'token.php';

header('Content-Type: application/json');

$basePath = $_SERVER['SCRIPT_NAME'];
$uri = str_replace($basePath, "", strtok($_SERVER["REQUEST_URI"], '?'));

switch ($uri) {
    case "/analytics/user":
        getUserById($_GET['id']);
        break;
    case "/analytics/streams":
        getStreams();
        break;
    case "/analytics/streams/enriched":
        getEnrichedStreams($_GET['limit']);
        break;
    case "/analytics/register":
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
    case "/analytics/topsofthetops":
        getTopOfTops($_GET['since']);
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}