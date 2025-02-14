<?php

include_once 'conectarBBDD.php';

function comprobarExpiracion($token) {
    $db = conectarBBDD();
    $stmt = $db->prepare("SELECT fechaexpiracion FROM usuario WHERE token = :token");
    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        return false;
    }
    return ($result['fechaexpiracion'] > date("Y-m-d H:i:s"));
}