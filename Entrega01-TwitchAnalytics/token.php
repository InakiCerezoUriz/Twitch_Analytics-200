<?php

include_once 'funcionesAuxiliares/conectarBBDD.php';
include './funcionesAuxiliares/generarToken.php';

function token($email, $api_key) {
    $db = conectarBBDD();

    if (empty($email)) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => "The email is mandatory"], JSON_PRETTY_PRINT);
        return;
    }

    // comprobar si el email es válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => "The email must be a valid email address"], JSON_PRETTY_PRINT);
        return;
    }

    if (empty($api_key)) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => "The api_key is mandatory"], JSON_PRETTY_PRINT);
        return;
    }


    if (!$db) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => "Internal Server error."], JSON_PRETTY_PRINT);
        return;
    } else{

        $stmt = $db->prepare("SELECT api_key, email FROM usuario WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($result)) {
            // El usuario no existe
            header("HTTP/1.1 401 Not Found");
            echo json_encode(["error"=> "Unauthorized. API access token is invalid."], JSON_PRETTY_PRINT);
            return;
        } else{
            if($result['api_key'] != $api_key || $result['email'] != $email){
                header("HTTP/1.1 401 Not Found");
                echo json_encode(["error"=> "Unauthorized. API access token is invalid."], JSON_PRETTY_PRINT);
                return;
            } else{
                // El usuario es de verdad - Se ha comprobado que el email y el api_key son correctos
                // Comprobar si el usuario ya tiene token
                $stmt = $db->prepare("SELECT token, fechaExpiracion FROM usuario WHERE email = :email");
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result['token'] == NULL || $result['fechaexpiracion'] < date("Y-m-d H:i:s")) {
                    // El usuario no tiene token
                    $resultado = generarToken();
                    $stmt = $db->prepare("UPDATE usuario SET token = :token, fechaExpiracion = :fechaExpiracion WHERE email = :email");
                    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $stmt->bindValue(':token', $resultado['token'], PDO::PARAM_STR);
                    $stmt->bindValue(':fechaExpiracion', $resultado['expiracion'], PDO::PARAM_STR);
                    $stmt->execute();
                    header("HTTP/1.1 200 Ok");
                    echo json_encode(["token"=> $resultado["token"]], JSON_PRETTY_PRINT);
                    return;
                } else{
                    // El token no ha expirado
                    header("HTTP/1.1 200 Ok");
                    echo json_encode(["token"=> $result["token"]], JSON_PRETTY_PRINT);
                    return;
                }

            }
        }
    }
    
}
?>
