<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'getUserById.php';
include 'getStreams.php';
include 'getEnrichedStreams.php';

$uri = strtok($_SERVER["REQUEST_URI"], '?');
header('Content-Type: application/json');

if ($_SERVER['SERVER_NAME'] == 'localhost') {
    switch ($uri) {
        case "/index.php/analytics/user":
            getUserById($_GET['id']);
            break;
        case "/index.php/analytics/streams":
            getStreams();
            break;
        case "/index.php/analytics/streams/enriched":
            getEnrichedStreams($_GET['limit']);
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
    }
}

