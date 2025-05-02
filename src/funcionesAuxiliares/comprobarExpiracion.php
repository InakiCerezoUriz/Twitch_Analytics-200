<?php

namespace TwitchAnalytics\funcionesAuxiliares;

function comprobarExpiracion($token): bool
{
    include_once 'conectarBBDD.php';

    $db   = conectarBBDD();
    $stmt = $db->prepare('SELECT fechaexpiracion FROM usuario WHERE token = :token');
    $stmt->bindValue(':token', $token);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        return false;
    }
    return ($result['fechaexpiracion'] > date('Y-m-d H:i:s'));
}
