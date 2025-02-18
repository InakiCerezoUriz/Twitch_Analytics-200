<?php

include_once 'funcionesAuxiliares/conectarBBDD.php';
require_once './funcionesAuxiliares/conseguirToken.php';
require_once './funcionesAuxiliares/comprobarExpiracion.php';

function getTopOfTops($since) {
    

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
          // Introducir datos en BBDD
          $db = conectarBBDD();
          $db->exec("DELETE FROM cache"); 
          // FALTA VACIAR LA TABLA
          for($i = 0; $i < 3; $i++) {
            $game_id = $data['data'][$i]['id'];
            $game_name = $data['data'][$i]['name'];
            // llamada a la segunda API 
            $api_url = "https://api.twitch.tv/helix/videos?game_id=$game_id&first=40&sort=views";
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

            $game_data = json_decode($response, true);
            for($j = 0;$j < 40; $j++){
              $user_name = $game_data['data'][$j]['user_name'];
              $title = $game_data['data'][$j]['title'];
              $views = $game_data['data'][$j]['view_count'];
              $duration = $game_data['data'][$j]['duration'];
              $created_at = $game_data['data'][$j]['created_at'];
              $ultima_solicitud = date('Y-m-d H:i:s');
              $insertStmt = $db->prepare("INSERT INTO cache (game_id, game_name, ultima_solicitud, user_name, title, views, duration, created_at) VALUES (:game_id, :game_name, :ultima_solicitud, :user_name, :title, :views, :duration, :created_at)");
              $insertStmt->bindValue(':game_id', $game_id, PDO::PARAM_STR);
              $insertStmt->bindValue(':game_name', $game_name, PDO::PARAM_STR);
              $insertStmt->bindValue(':ultima_solicitud', $ultima_solicitud, PDO::PARAM_STR);
              $insertStmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
              $insertStmt->bindValue(':title', $title, PDO::PARAM_STR);
              $insertStmt->bindValue(':views', $views, PDO::PARAM_STR);
              $insertStmt->bindValue(':duration', $duration, PDO::PARAM_STR);
              $insertStmt->bindValue(':created_at', $created_at, PDO::PARAM_STR);
              $insertStmt->execute();
            }
          }
          $selectStmt = $db->prepare("SELECT DISTINCT user_name FROM CACHE");
          $selectStmt->execute();
          $nombres = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

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