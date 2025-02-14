<?php

require_once './funcionesAuxiliares/conseguirToken.php';
require_once './funcionesAuxiliares/comprobarExpiracion.php';

function getEnrichedStreams($limit) {
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

  $api_url = 'https://api.twitch.tv/helix/streams';
  $token = conseguirToken();

  $headers = [
    "Authorization: Bearer $token",
    'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',
    'Content-Type: application/json',
  ];

  // Validar que 'limit' sea un número entero válido y esté en el rango adecuado
  $limit = filter_var($_GET['limit'], FILTER_VALIDATE_INT);

  if ($limit === false || $limit <= 0 || $limit > 20) {
    header("HTTP/1.1 400 Bad Request");
    header('Content-Type: application/json');
    echo json_encode(
                ["error" => " Invalid 'limit' parameter."
    ]);
    exit();
  }

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
      #Obetener del data los datos que queremos (title, user_name)
      for( $i = 0; $i < $_GET['limit']; $i++ ){
        #Obtner el id del usuario para hacer una petición a la API de twitch
        $user_id  = $data['data'][$i]['user_id'];
        $api_url = 'https://api.twitch.tv/helix/users?id=' . $user_id;
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $res = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data_user = json_decode($response, true);



        #Formar el array con los datos que queremos
        $stream_id = $data['data'][$i]['id'];
        $user_name = $data['data'][$i]['user_name'];
        $viwer_count = $data['data'][$i]['viewer_count'];
        $title = $data['data'][$i]['title'];
        $user_display_name = $data_user['data'][0]['display_name'];
        $profile_image_url = $data_user['data'][0]['profile_image_url'];
        $lista[$i] = [
          'stream_id' => $stream_id,
          'user_id' => $user_id,
          'user_name' => $user_name,
          'viwer_count' => $viwer_count,
          'title'=> $title,
          'user_display_name' => $user_display_name,
          'profile_image_url' => $profile_image_url
        ];
      }
      $lista = json_encode($lista, JSON_PRETTY_PRINT);
      echo $lista;
      break;
    case 400:
      header("HTTP/1.1 400 Bad Request");
      echo json_encode(['error' => "Invalid or missing 'limit' parameter."], JSON_PRETTY_PRINT);
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
?>
