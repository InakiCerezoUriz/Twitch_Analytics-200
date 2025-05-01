<?php

function token($email, $api_key)
{
    include_once 'funcionesAuxiliares/conectarBBDD.php';
    include_once './funcionesAuxiliares/generarToken.php';

    $db = conectarBBDD();

    //Comprueba si el email existe y si es válido
    $error = validarEmail($email);
    if ($error !== null) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $error], JSON_PRETTY_PRINT);
        return;
    }

    $error = validarApiKey($api_key);
    if ($error !== null) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => $error], JSON_PRETTY_PRINT);
        return;
    }


    if (!$db) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Internal Server error.'], JSON_PRETTY_PRINT);
        return;
    }

    $usuario = obtenerUsuario($db, $email);

    if (!$usuario || $usuario['api_key'] != $api_key || $usuario['email'] != $email) {
        header('HTTP/1.1 401 Not Found');
        echo json_encode(['error' => 'Unauthorized. API access token is invalid.'], JSON_PRETTY_PRINT);
        return;
    }
    // El usuario es de verdad - Se ha comprobado que el email y el api_key son correctos
    $resultado = emitirToken($usuario, $db);

    http_response_code(200);
    echo json_encode($resultado, JSON_PRETTY_PRINT);
}

function validarEmail(string $email): ?string
{
    if (empty($email)) {
        return 'The email is mandatory';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'The email must be a valid email address';
    }
    return null;
}

function validarApiKey(string $api_key): ?string
{
    return empty($api_key) ? 'The api_key is mandatory' : null;
}

function obtenerUsuario(PDO $db, string $email): ?array
{
    $stmt = $db->prepare('SELECT api_key, email, token, fechaExpiracion FROM usuario WHERE email = :email');
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function emitirToken(array $usuario, PDO $db): array
{
    if ($usuario['token'] === null || $usuario['fechaExpiracion'] < date('Y-m-d H:i:s')) {
        $nuevoToken = generarToken();
        $stmt       = $db->prepare('UPDATE usuario SET token = :token, fechaExpiracion = :fechaExpiracion WHERE email = :email');
        $stmt->bindValue(':token', $nuevoToken['token'], PDO::PARAM_STR);
        $stmt->bindValue(':fechaExpiracion', $nuevoToken['expiracion'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $usuario['email'], PDO::PARAM_STR);
        $stmt->execute();
        return ['token' => $nuevoToken['token']];
    }
    return ['token' => $usuario['token']];
}
