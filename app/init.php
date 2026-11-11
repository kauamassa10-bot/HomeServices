<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/banco/database.php';
require_once __DIR__ . '/banco/crud.php';

$pdo = database();

function pegarIniciais($nome) {
    $partes = array_values(array_filter(explode(' ', trim($nome))));
    $iniciais = '';
    if (isset($partes[0])) $iniciais .= mb_substr($partes[0], 0, 1);
    if (isset($partes[1])) $iniciais .= mb_substr($partes[1], 0, 1);
    return mb_strtoupper($iniciais);
}

