<?php
const API_URL = 'https://api.twitch.tv/helix/users?login=auronplay';

$ch = curl_init(API_URL);

$headers = [
    'Authorization: Bearer cdrpstu2cmupxijmlcr6vlmdg7ugrg',  // Token
    'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
    'Content-Type: application/json',
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$user = $data['data'][0];
?>

<head>
  <meta charset="UTF-8" />
  <title>Buscar usuarios de twitch</title>
  <meta name="description" content="Buscar usuarios de twitch" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.classless.min.css" />
</head>

<main>
    <section>
        <img src="<?= $user["profile_image_url"]; ?>" width="200" alt="Imagen de <?= $user["display_name"]; ?>" />  <!-- Mostramos la imagen del canal -->
    </section>
    <hgroup>
    <h3><?= $user["display_name"]; ?> es <?= $user["broadcaster_type"]; ?></h3>
    <p>Descripción: <?= $user["description"]; ?></p>
  </hgroup>
  <section>
        <img src="<?= $user["offline_image_url"]; ?>" width="200" alt="Imagen offline de <?= $user["display_name"]; ?>" />  <!-- Mostramos la imagen del canal -->
    </section>
    Codigo devuelto por la api: 
  <pre style="font-size: 12px; overflow:scroll; height:200px; width: 200px; white-space: pre-wrap; word-wrap: break-word; color: yellow;">
    <?php echo $response; ?> <!-- Mostramos la respuesta de la api -->
  </pre>
</main>

<style>
  :root {
    color-scheme: light dark;
  }
  body {
    display: grid;
    place-content: center;
  }
  section {
    display: flex;
    justify-content: center;
    text-align: center;
  }
  hgroup {
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
  }
  img {
    margin: 0 auto;
  }
</style>
