<?php

require_once '../../funcionesAuxiliares/conseguirToken.php';

$api_url = 'https://api.twitch.tv/helix/users?id=';

$token = conseguirToken();

#Conseguir el token con curl haciendo una petición POST para logearse
$headers = [
    "Authorization: Bearer $token",  // Token
    'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
    'Content-Type: application/json',
];

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (isset($_GET['id'])){
      // Mostrar canal por id
      $api_url .= $_GET['id'];

      $ch = curl_init($api_url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      if($_SERVER['SERVER_NAME'] == 'localhost'){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      }

      $response = curl_exec($ch);
      $res = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      curl_close($ch);

      header('Content-Type: application/json');
      $data = json_decode($response, true);
      switch ($res){
        case 200:
          if(empty($data['data'])){
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => "User not found."], JSON_PRETTY_PRINT);
          }
          else{
            header("HTTP/1.1 200 Ok");
            $user = json_encode($data['data'][0], JSON_PRETTY_PRINT);
            echo $user; 
          }        
          break;
        case 400:
          header("HTTP/1.1 400 Bad Request");
          echo json_encode(['error' => "Invalid or missing 'id' parameter."], JSON_PRETTY_PRINT);
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

      exit();
    }
    header("HTTP/1.1 400 Bad Request");
    header('Content-Type: application/json');
    echo json_encode("error: Invalid or missing 'id' parameter.", JSON_PRETTY_PRINT);
}
?>
