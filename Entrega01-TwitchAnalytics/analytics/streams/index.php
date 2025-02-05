<?php

    #Conseguir el token con curl haciendo una petición POST para logearse
    $headers = [
        'Authorization: Bearer cdrpstu2cmupxijmlcr6vlmdg7ugrg',  // Token
        'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
        'Content-Type: application/json',
    ];

    if (!isset($_GET['id'])){
      $api_url = 'https://api.twitch.tv/helix/streams';
      $ch = curl_init($api_url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $response = curl_exec($ch);
      $res = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      curl_close($ch);
      header('Content-Type: application/json; Charset: UTF-8');

      switch ($res){
        case 200:
          header("HTTP/1.1 200 Ok");
          $data = json_decode($response, true);
          #Obetener del data los datos que queremos (title, user_name)
          for( $i = 0; $i < count($data['data']); $i++ ){
            $title = $data['data'][$i]['title'];
            $user_name = $data['data'][$i]['user_name'];
            $lista[$i] = [
              'title' => $title,
              'user_name' => $user_name
            ];
          }
          $lista = json_encode($lista, JSON_PRETTY_PRINT);
          print($lista);
          break;
        case 401:
          header("HTTP/1.1 401 Unauthorized");
          echo json_encode("error: Unauthorized. Twitch access token is invalid or has expired.", JSON_PRETTY_PRINT);
          break;
        case 500:
          header("HTTP/1.1 500 Internal Server Error");
          echo json_encode("error: Internal Server error.", JSON_PRETTY_PRINT);
          break;
      }

      exit();
    }

?>
