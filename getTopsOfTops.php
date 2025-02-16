<?php

include_once 'funcionesAuxiliares/conectarBBDD.php';
require_once './funcionesAuxiliares/conseguirToken.php';
require_once './funcionesAuxiliares/comprobarExpiracion.php';

function getTopOfTops($since) {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => "Authorization header is missing."], JSON_PRETTY_PRINT);
        exit();
    }
    
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $token = str_replace('Bearer ', '', $authHeader);
      
    if(!comprobarExpiracion($token)) {
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(['error' => "Unauthorized. Token is invalid or has expired."], JSON_PRETTY_PRINT);
        return;
    }

    $token = conseguirToken();

    $headers = [
    "Authorization: Bearer $token",
    'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',
    'Content-Type: application/json',
    ];

    // Si 'since' es vacío, le asigna 600 segundos, si no, comprueba si es un entero.
    if(empty($_GET['since'])) {
      $since = 600;
    }
    else {
      $since = filter_var($_GET['since'], FILTER_VALIDATE_INT);
      if ($since === false) {
        header("HTTP/1.1 400 Bad Request");
        header('Content-Type: application/json');
        echo json_encode(
                    ["error" => " Invalid 'since' parameter."
        ]);
        exit();
      }
    }

    //FALTA COMPROBAR SI EL SINCE > ULTIMA_SOLICITUD

    $api_url = 'https://api.twitch.tv/helix/games/top?first=3';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    
    $response = curl_exec($ch);
    $res = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    
    switch ($res){
        case 200:
          header("HTTP/1.1 200 Ok");
          header('Content-Type: application/json');
          $data = json_decode($response, true);
          echo json_encode($data);
          // Introducir datos en BBDD
          $db = conectarBBDD();
          for($i = 0; $i < 3; $i++) {
            $game_id = $data['data'][$i]['id'];
            $game_name = $data['data'][$i]['name'];
            $ultima_solicitud = date('Y-m-d H:i:s');
            $insertStmt = $db->prepare("INSERT INTO cache (game_id, game_name, ultima_solicitud) VALUES (:game_id, :game_name, :ultima_solicitud)");
            $insertStmt->bindValue(':game_id', $game_id, PDO::PARAM_STR);
            $insertStmt->bindValue(':game_name', $game_name, PDO::PARAM_STR);
            $insertStmt->bindValue(':ultima_solicitud', $ultima_solicitud, PDO::PARAM_STR);
            $insertStmt->execute();
          }

          break;
        case 401:
          header("HTTP/1.1 401 Unauthorized");
          echo json_encode(['error' =>  "Unauthorized. Twitch access token is invalid or has expired."], JSON_PRETTY_PRINT);
          break;
        case 500:
          header("HTTP/1.1 500 Internal Server Error");
          echo json_encode(['error' =>  "Internal Server error."], JSON_PRETTY_PRINT);
          break;
      }
}