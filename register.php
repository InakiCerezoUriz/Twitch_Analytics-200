<?php

function register()
{
    include_once __DIR__ . '/src/funcionesAuxiliares/conectarBBDD.php';
    include_once __DIR__ . '/src/funcionesAuxiliares/generarApiKey.php';

    $body  = file_get_contents('php://input');
    $data  = json_decode($body, true);
    $email = $data['email'] ?? null;
    if (empty($email)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'The email is mandatory'], JSON_PRETTY_PRINT);
        return;
    }

    // comprobar si el email es válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'The email must be a valid email address'], JSON_PRETTY_PRINT);
        return;
    }

    $db = conectarBBDD();

    if (!$db) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Internal Server error.'], JSON_PRETTY_PRINT);
        return;
    } else {
        $stmt = $db->prepare('SELECT api_key FROM usuario WHERE email = :email');
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            // El usuario tiene api_key
            header('HTTP/1.1 200 Ok');
            $newApiKey = generateApiKey();
            echo json_encode(['api_key' => $newApiKey], JSON_PRETTY_PRINT);

            // Actualizar token en la base de datos
            $updateStmt = $db->prepare('UPDATE usuario SET api_key = :api_key WHERE email = :email');
            $updateStmt->bindValue(':api_key', $newApiKey, PDO::PARAM_STR);
            $updateStmt->bindValue(':email', $email, PDO::PARAM_STR);
            $updateStmt->execute();
            return;
        } else {
            // El usuario no tiene token
            header('HTTP/1.1 200 Ok');
            $newApiKey = generateApiKey();
            echo json_encode(['api_key' => $newApiKey], JSON_PRETTY_PRINT);

            // Insertar email y token en la base de datos
            $insertStmt = $db->prepare('INSERT INTO usuario (email, api_key) VALUES (:email, :api_key)');
            $insertStmt->bindValue(':email', $email, PDO::PARAM_STR);
            $insertStmt->bindValue(':api_key', $newApiKey, PDO::PARAM_STR);
            $insertStmt->execute();
            return;
        }
    }
}
