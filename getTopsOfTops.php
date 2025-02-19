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
  
  if(filter_var($since, FILTER_VALIDATE_INT) == false){
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => "Bad Request. Invalid or missing parameters."], JSON_PRETTY_PRINT);
    return;
  }

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
        
      // Comprobar la ultima actualizacion de la base de datos
      $stmt = $db->prepare('SELECT ultima_solicitud FROM cache ORDER BY ultima_solicitud DESC LIMIT 1');
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $ultima_solicitud = strtotime($result[0]['ultima_solicitud']);
      $current_time = time();
      if (($current_time - $ultima_solicitud) > $since) {
        $db->exec("DELETE FROM cache"); // Limpiar la tabla
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
      }

      $selectStmt = $db->prepare("SELECT GAME_NAME, USER_NAME
                                          FROM CACHE
                                          GROUP BY GAME_NAME, USER_NAME
                                          ORDER BY GAME_NAME, USER_NAME;");
      $selectStmt->execute();
      $nombres = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

      for($k = 0; $k < count($nombres); $k++){
        $selectStmt = $db->prepare("WITH MaxViews AS (
                                                              SELECT 
                                                                  GAME_ID,
                                                                  GAME_NAME,
                                                                  USER_NAME,
                                                                  TITLE,
                                                                  VIEWS,
                                                                  DURATION,
                                                                  CREATED_AT,
                                                                  ROW_NUMBER() OVER (PARTITION BY GAME_ID, USER_NAME ORDER BY VIEWS DESC) AS row_num
                                                              FROM 
                                                                  CACHE
                                                          )
                                                          SELECT 
                                                              c.GAME_ID,
                                                              c.GAME_NAME,
                                                              c.USER_NAME,
                                                              COUNT(*) AS TOTAL_VIDEOS,
                                                              SUM(c.VIEWS) AS TOTAL_VIEWS,
                                                              mv.TITLE AS MOST_VIEWED_TITLE,
                                                              mv.VIEWS AS MOST_VIEWED_VIEWS,
                                                              mv.DURATION AS MOST_VIEWED_DURATION,
                                                              mv.CREATED_AT AS MOST_VIEWED_CREATED_AT
                                                          FROM 
                                                              CACHE c
                                                          JOIN 
                                                              MaxViews mv ON c.GAME_ID = mv.GAME_ID AND c.USER_NAME = mv.USER_NAME AND mv.row_num = 1
                                                          WHERE 
                                                              c.GAME_NAME = :game_name AND c.USER_NAME = :user_name
                                                          GROUP BY 
                                                              c.GAME_ID, c.GAME_NAME, c.USER_NAME, mv.TITLE, mv.VIEWS, mv.DURATION, mv.CREATED_AT
                                                          ORDER BY 
                                                              TOTAL_VIEWS DESC;");
        $selectStmt->bindValue(":game_name", $nombres[$k]['game_name'], PDO::PARAM_STR);
        $selectStmt->bindValue(":user_name", $nombres[$k]['user_name'], PDO::PARAM_STR);
        $selectStmt->execute();
        while ($tops = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
          $final[] = $tops;
        }
      }
      print(json_encode($final, JSON_PRETTY_PRINT));

      break;
    case 401:
      header("HTTP/1.1 401 Unauthorized");
      echo json_encode(['error' =>  "Unauthorized. Twitch access token is invalid or has expired."], JSON_PRETTY_PRINT);
      break;
    case 404:
      header("HTTP/1.1 404 Not Found");
      echo json_encode(['error' =>  "Not Found. No data available."], JSON_PRETTY_PRINT);
    case 500:
      header("HTTP/1.1 500 Internal Server Error");
      echo json_encode(['error' =>  "Internal Server error."], JSON_PRETTY_PRINT);
      break;
  }
}