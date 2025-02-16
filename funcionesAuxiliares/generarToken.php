<?php
function generarToken() {
    $token = bin2hex(random_bytes(16));
    $expiracion = date('Y-m-d H:i:s', strtotime('+3 days'));
    return array('token' => $token, 'expiracion' => $expiracion);
}